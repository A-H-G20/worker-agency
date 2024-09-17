// Function to toggle the visibility of the popup
function togglePopup(element) {
    const popup = document.getElementById('popup-box');

    // Get the position of the clicked element (the 3 dots)
    const rect = element.getBoundingClientRect();

    // Set the popup position relative to the button
    popup.style.top = rect.top + window.scrollY + "px";
    popup.style.left = rect.left + rect.width + 10 + "px";  // Adds spacing from the 3-dot button

    // Toggle the display of the popup
    if (popup.style.display === "none" || popup.style.display === "") {
        popup.style.display = "block";
    } else {
        popup.style.display = "none";
    }
}
