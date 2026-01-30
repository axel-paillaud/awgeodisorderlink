document.addEventListener('DOMContentLoaded', () => {
  const transmitBtn = document.querySelector('.js-transmit');

  if (!transmitBtn || !orderId) return;

  transmitBtn.addEventListener('click', () => {
    // Fire and forget: send request to update order status
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

    // Let the native click behavior continue (page reload)
  });
});
