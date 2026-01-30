document.addEventListener('DOMContentLoaded', () => {
  const STORAGE_KEY = 'awgeodis_order_id';

  // Store orderId in sessionStorage if available from PHP
  if (typeof orderId !== 'undefined' && orderId) {
    sessionStorage.setItem(STORAGE_KEY, orderId);
  }

  // Retrieve orderId from sessionStorage (works after page reload)
  const storedOrderId = sessionStorage.getItem(STORAGE_KEY);

  const transmitBtn = document.querySelector('.js-transmit');

  if (!transmitBtn || !storedOrderId) return;

  transmitBtn.addEventListener('click', () => {
    // Use sendBeacon to ensure request survives page navigation
    const data = JSON.stringify({ orderId: storedOrderId });
    const blob = new Blob([data], { type: 'application/json' });

    navigator.sendBeacon(awGeodisOrderLinkUpdateStateOrderAjaxControllerUri, blob);

    // Clear stored orderId after transmission
    sessionStorage.removeItem(STORAGE_KEY);

    // Let the native click behavior continue (page reload)
  });
});
