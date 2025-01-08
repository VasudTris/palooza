document.addEventListener("DOMContentLoaded", function () {

  const collapseElements = document.querySelectorAll(".collapse");

  collapseElements.forEach((collapse) => {
    collapse.addEventListener("show.bs.collapse", function () {
      this.style.height = "0px";

      this.style.maxHeight = "10rem";
    });

    collapse.addEventListener("hide.bs.collapse", function () {
      this.style.maxHeight = "";
    });
  });

  document
    .getElementById("addQuestionBtn")
    .addEventListener("click", function () {
      let vraagField = document.querySelector('input[name="vraag"]');
      let vraagText = vraagField.value;

      if (vraagText) {
        fetch("/add-question", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: JSON.stringify({ question: vraagText }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              let ul = document.getElementById("vragenlijst");
              let li = document.createElement("li");
              li.innerHTML = `
                                    <strong>${vraagText}</strong>
                                    <button class="delete-question-btn" data-question-id="${data.questionId}">X</button>
                                    <ul>
                                        <li class="mb-2">
                                            <input type="text" class="antwoord-input me-1 " placeholder="Antwoord 1">
                                            <input type="checkbox" class="correct" id="correct" name="correct" value="correct">
                                         </li>
                                        <li>
                                            <button class="save-answers-btn" data-question-id="${data.questionId}">Opslaan</button>
                                        </li>
                                    </ul>
                                `;
              ul.appendChild(li);
              vraagField.value = "";
            } else {
              alert(
                "er is een fout opgetreden bij het toevoegen van de vraag!!!!!!:()."
              );
            }
          });
      }
    });

  // -------------------------------------------------------------------------------------------------------------------
  //! voor extra antwoord veld
  document.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      let target = e.target;
      if (target && target.classList.contains("antwoord-input")) {
        let ul = target.closest("ul");

        let li = document.createElement("li");
        li.classList.add("mb-2");

        // maakt het nieuwe invoerveld aan
        let newInput = document.createElement("input");
        newInput.type = "text";
        newInput.className = "antwoord-input me-2 ";
        newInput.placeholder =
          "Antwoord " + (ul.querySelectorAll(".antwoord-input").length + 1);

        // maakt de nieuwe checkbox aan
        let newCheckbox = document.createElement("input");
        newCheckbox.type = "checkbox";
        newCheckbox.className = "correct";
        newCheckbox.name = "correct";
        newCheckbox.value = "correct";

        li.appendChild(newInput);
        li.appendChild(newCheckbox);

        ul.appendChild(li);
        newInput.focus();
      }
    }
  });

  // -------------------------------------------------------------------------------------------------------------------
  //! verwijdere van een vraag
  document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("delete-question-btn")) {
      let questionId = e.target.getAttribute("data-question-id");
      if (confirm("Weet je zeker dat je deze vraag wilt verwijderen?")) {
        fetch(`/delete-question/${questionId}`, {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        }).then((response) => {
          if (response.ok) {
            e.target.closest(".accordion-item").remove(); // vewijderd het gehele accordion-item
          } else {
            alert(
              "er is een fout opgetreden bij het verwijderen van de vraag."
            );
          }
        });
      }
    }
  });
  // -------------------------------------------------------------------------------------------------------------------
  //! opslaan van de antwoorden
  document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("save-answers-btn")) {
      let questionId = e.target.getAttribute("data-question-id");
      let answers = [];
      let correctAnswer = false;

      e.target
        .closest("ul")
        .querySelectorAll(".antwoord-input")
        .forEach((input) => {
          let checkbox = input.nextElementSibling;
          let isCorrect = checkbox.checked;
          answers.push({ answer: input.value.trim(), isCorrect: isCorrect });
          if (isCorrect) {
            correctAnswer = true;
          }
        });
      fetch("/save-answers", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ questionId: questionId, answers: answers }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Antwoorden opgeslagen");
            if (correctAnswer) {
              e.target.closest(".accordion-item").classList.add("bg-purple");
            }
          } else {
            alert("Er is een fout opgetreden");
          }
        });
    }
  });
});
