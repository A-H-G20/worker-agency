document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.querySelector('.add');
    const addPostForm = document.getElementById('add-post-form');
    const cancelButton = document.querySelector('.cancel');

    // Show the form when the "Add" button is clicked
    addButton.addEventListener('click', function() {
        addPostForm.style.display = 'block';
    });

    // Hide the form when the "Cancel" button is clicked
    cancelButton.addEventListener('click', function() {
        addPostForm.style.display = 'none';
    });

    // Optionally, you can hide the form after successful form submission
    const form = addPostForm.querySelector('form');
    form.addEventListener('submit', function() {
        addPostForm.style.display = 'none';
    });
});