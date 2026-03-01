(function (window) {
    const Zora = window.Zora = window.Zora || {};

    // =========================
    // CORE HELPERS
    // =========================

    Zora.extend = function (methods) {
        Object.keys(methods || {}).forEach((k) => {
            Zora[k] = methods[k];
        });
        return Zora;
    };

    // =========================
    // DEFAULT UTILITIES
    // =========================

    Zora.extend({
        toRupiah(value) {
            const number = Number(value) || 0;
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }
    });

    document.addEventListener("DOMContentLoaded", function () {

        const token = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (token && typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
    });

    $(document).on("click", ".action-save", function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Attention!',
            text: "Are you sure you want to save this?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Save',
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.value) {
                form.submit();
            }
        });
    });

    $(document).on("click", ".action-delete", function (e) {
        e.preventDefault();
        const button = $(this);
        const url = button.data("url");
        const form = button.closest("form");

        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: "Are you sure you want to delete this?",
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (!result.isConfirmed) return;
            if (url) {
                button.prop('disabled', true);
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    success: function (response) {
                        const tableElement = button.closest('table');
                        if ($.fn.DataTable &&
                            tableElement.length &&
                            $.fn.DataTable.isDataTable(tableElement)) {
                            tableElement.DataTable().ajax.reload(null, false);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        let message = 'Failed to delete data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    },
                    complete: function () {
                        button.prop('disabled', false);
                    }
                });
            } else if (form.length) {
                form.submit();
            }
        });
    });

})(window);
