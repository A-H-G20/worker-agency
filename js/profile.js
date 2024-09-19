function togglePopup(postId) {
  var popup = document.getElementById('popup-' + postId);
  popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
}

function deletePost(postId) {
  document.getElementById('delete-post-form').post_id.value = postId;
  document.getElementById('delete-post-form').submit();
}