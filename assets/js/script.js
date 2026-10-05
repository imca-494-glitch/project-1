document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchInput');
  const tableBody = document.getElementById('donationTableBody');

  if (searchInput && tableBody) {
    searchInput.addEventListener('input', () => {
      const filter = searchInput.value.toLowerCase();
      const rows = Array.from(tableBody.querySelectorAll('tr'));

      rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });
  }
});
