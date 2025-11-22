$(document).ready(function () {
    console.log('sdsd');

    $('.action-save').on('click', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Perhatian!',
            text: "Apakah anda akan menyimpan data?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Simpan',
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.value) {
                form.submit();
            }
        });
    });

    $('.action-delete').on('click', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: "Apakah anda akan menghapus data?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.value) {
                const form = e.target.closest('form');
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                form.appendChild(actionInput);
                form.submit();
            }
        });
    });
});
