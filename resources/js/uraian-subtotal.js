document.addEventListener('DOMContentLoaded', function () {
    const updateSubtotal = (hargaField, kuantitasField, subtotalField) => {
        const harga = parseFloat(hargaField.value) || 0;
        const kuantitas = parseFloat(kuantitasField.value) || 0;
        const subtotal = harga * kuantitas;
        subtotalField.value = subtotal.toFixed(2); // Atur subtotal dengan 2 desimal
    };

    document.querySelectorAll('[data-subtotal-field="subtotal"]').forEach((element) => {
        const hargaField = element.closest('.repeater-item').querySelector('[name="harga_per_unit"]');
        const kuantitasField = element.closest('.repeater-item').querySelector('[name="kuantitas"]');
        const subtotalField = element;

        // Event listener untuk mengupdate subtotal
        hargaField.addEventListener('input', () => {
            updateSubtotal(hargaField, kuantitasField, subtotalField);
        });
        kuantitasField.addEventListener('input', () => {
            updateSubtotal(hargaField, kuantitasField, subtotalField);
        });
    });
});
