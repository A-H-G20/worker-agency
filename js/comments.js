function togglePopup(commentId) {
  const popup = document.getElementById("popup-box");
  const deleteButton = document.getElementById("delete-comment-id");

  if (popup.style.display === "none" || !popup.style.display) {
    popup.style.display = "block";
    deleteButton.value = commentId;
  } else {
    popup.style.display = "none";
  }
}
document.addEventListener("DOMContentLoaded", function () {
  const popupBox = document.getElementById("popup-box");
  const deleteButton = document.getElementById("delete-button");
  const cancelButton = document.getElementById("cancel-button");

  // Function to show the popup with the comment ID
  function showPopup(commentId) {
    popupBox.style.display = "block";
    deleteButton.setAttribute("data-comment-id", commentId);
  }

  // Event listener for the delete button
  deleteButton.addEventListener("click", function () {
    const commentId = this.getAttribute("data-comment-id");
    if (commentId) {
      fetch("delete_comment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          comment_id: commentId,
        }),
      })
        .then((response) => response.text())
        .then((result) => {
          alert(result);
          popupBox.style.display = "none";
          // Optionally, you may want to refresh the comments list here
        })
        .catch((error) => {
          console.error("Error:", error);
        });
    }
  });

  // Event listener for the cancel button
  cancelButton.addEventListener("click", function () {
    popupBox.style.display = "none";
  });
});
