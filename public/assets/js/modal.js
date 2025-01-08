document.addEventListener("DOMContentLoaded", function () {
  const modalsContainer = document.getElementById("modalsContainer");
  const roundButton = document.getElementById("roundPlusButton");

  //! add functie
  roundButton.addEventListener("click", function () {
    const defaultModalName = "Unnamed Modal";
    fetch("/add-modal", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ nameModal: defaultModalName }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          //* dynamisch live toevoegen van klein modal
          //? eerste manier om iets live toe te voegen
          const newModal = document.createElement("div");
          newModal.classList.add("small-modal");
          newModal.setAttribute("data-modal-id", data.modalId);
          newModal.setAttribute("data-bs-toggle", "modal");
          newModal.setAttribute("data-bs-target", `#myModal-${data.modalId}`);
          newModal.textContent = defaultModalName;
          newModal.setAttribute("ondrop", "drop(event)");
          newModal.setAttribute("ondragover", "event.preventDefault();");
          modalsContainer.insertBefore(newModal, roundButton);

          //* dynamisch live de grote modal toevoegen
          //? twede manier om iets live toe te voegen
          const newLargeModal = `
                <div class="modal fade" style="color: black;" id="myModal-${data.modalId}" tabindex="-1" aria-labelledby="exampleModalLabel-${data.modalId}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel-${data.modalId}">Naam:</h5>
                                <input type="text" id="modalName-${data.modalId}" class="form-control" placeholder="Voer de naam van de modal in" value="${defaultModalName}">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" data-modal-id="${data.modalId}" style="color: black;">
                                <!-- Hier komen de vragen voor deze modal -->
                            </div>
                            <div class="modal-footer">
                                <button class="delete-modal-btn btn btn-danger btn-sm me-2 ms-2" data-modal-id="${data.modalId}">X</button>
                                <button type="button" class="btn btn-primary saveModalButton" data-modal-id="${data.modalId}">Opslaan</button>
                            </div>
                        </div>
                    </div>
                </div>`;

          //* ditvoegt de grote modal toe 
          document.body.insertAdjacentHTML("beforeend", newLargeModal);

          //* opent gelijk de modal na het teovoegen
          const modalOpen = new bootstrap.Modal(
            document.getElementById(`myModal-${data.modalId}`)
          );
          modalOpen.show();

          //! voor opslaan van nieuw modal
          document
            .querySelector(`.saveModalButton[data-modal-id="${data.modalId}"]`)
            .addEventListener("click", function () {
              const modalNameInput = document.querySelector(
                `#modalName-${data.modalId}`
              ).value;

              if (modalNameInput.trim() !== "") {
                fetch("/save-modal", {
                  method: "POST",
                  headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                  },
                  body: JSON.stringify({
                    modalId: data.modalId,
                    nameModal: modalNameInput,
                  }),
                })
                  .then((response) => response.json())
                  .then((saveData) => {
                    if (saveData.success) {
                      //* hier word naam van kleinmodal bewerkt
                      newModal.innerText = modalNameInput;
                      console.log(saveData.message);
                    } else {
                      console.error(
                        "er is een dikke fout jinguh: ",
                        saveData.message
                      );
                    }
                  })
                  .catch((error) => {
                    console.error("Error:", error);
                  });
              } else {
                console.error("Voer een naam in voor de modal.");
              }
            });
        } else {
          console.error("sukkel: ", data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  });

  //! delete functie
  document.querySelectorAll(".delete-modal-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const modalId = button.getAttribute("data-modal-id");
      fetch(`/delete-modal/${modalId}`, {
        method: "DELETE",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const modalElement = document.querySelector(
              `.small-modal[data-modal-id="${modalId}"]`
            );
            const largeModalElement = document.getElementById(
              `myModal-${modalId}`
            );

            if (modalElement) {
              modalElement.remove();
            }

            if (largeModalElement) {
              const bootstrapModal =
                bootstrap.Modal.getInstance(largeModalElement);
              if (bootstrapModal) {
                bootstrapModal.hide();
              }
              largeModalElement.remove();
            }

            console.log(data.message);
          } else {
            console.error("Er is een fout opgetreden:", data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
        });
    });
  });

  //! save functie
  // document.querySelectorAll(".saveModalButton").forEach((button) => {
  //   button.addEventListener("click", function () {
  //     const modalId = button.getAttribute("data-modal-id");
  //     const modalNameInput = document.querySelector(
  //       `#modalName-${modalId}`
  //     ).value;

  //     if (modalNameInput.trim() !== "") {
  //       fetch("/save-modal", {
  //         method: "POST",
  //         headers: {
  //           "Content-Type": "application/json",
  //           "X-Requested-With": "XMLHttpRequest",
  //         },
  //         body: JSON.stringify({ modalId: modalId, nameModal: modalNameInput }),
  //       })
  //         .then((response) => response.json())
  //         .then((data) => {
  //           if (data.success) {
  //             console.log(data.message);
  //             document.querySelector(
  //               `.small-modal[data-modal-id="${modalId}"]`
  //             ).innerText = modalNameInput;
  //           } else {
  //             console.error("Er is een fout opgetreden:", data.message);
  //           }
  //         })
  //         .catch((error) => {
  //           console.error("Error:", error);
  //         });
  //     } else {
  //       console.error("Voer een naam in voor de modal.");
  //     }
  //   });
  // });
});
