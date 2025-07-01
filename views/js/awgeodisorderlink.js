document.addEventListener('DOMContentLoaded', () => {
  const updateBtn = document.getElementById('geodis-link-update-status');

  updateBtn.addEventListener('click', () => {
    fetch(awGeodisOrderLinkUpdateStateOrderAjaxControllerUri, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        orderId: orderId,
      }),
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showSuccessMessage(data.message);
      } else {
        showErrorMessage(data.message);
      }
    })
    .catch(error => {
      console.error(error);
      showErrorMessage(error.message);
    });
  });
});
