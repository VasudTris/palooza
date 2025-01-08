function allowDrop(event) {
  event.preventDefault();
}

function drag(event) {
  const questionId = event.target.dataset.questionId;
  if (questionId) {
    event.dataTransfer.setData("text", questionId);
  } else {
    console.error("geen questionId gevonden op het element");
  }
}



function addQuestionToModal(questionId, modalId) {
  return fetch("/add-questionToModal", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      questionId: questionId,
      modalId: modalId,
    }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("slechte poep internet");
      }
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        updateLargeModal(modalId);
        return data;
      } else {
        throw new Error(data.message);
      }
    });
}

function updateLargeModal(modalId) {
    fetch(`/get-modalQuestions/${modalId}`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
    .then((response) => response.json())
    .then((data) => {
        const modalBody = document.querySelector(`#myModal-${modalId} .modal-body`);
        modalBody.innerHTML = "";
        data.questions.forEach((question) => {
            const questionElement = document.createElement("div");
            questionElement.innerText = question;
            modalBody.appendChild(questionElement);
        });
    })
    .catch((error) => console.error("Error:", error));
}

function drop(event) {
  event.preventDefault();
  const questionId = event.dataTransfer.getData("text");
  const modalId = event.target.dataset.modalId;

  if (questionId && modalId) {
    addQuestionToModal(questionId, modalId).then(() => {
      updateLargeModal(modalId);
    });
  }
}