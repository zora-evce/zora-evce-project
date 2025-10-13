<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment - Midtrans Sandbox</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Midtrans Snap.js Sandbox -->
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <style>
        body { font-family: sans-serif; padding: 40px; }
        button {
            background-color: #0099cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        button:disabled {
            background: #ccc;
        }
    </style>
</head>
<body>
    <h2>Contoh Pembayaran Midtrans (Sandbox)</h2>
    <p>Total Pembayaran: <b>Rp 100.000</b></p>

    <button id="pay-button">Bayar Sekarang</button>

    <script>
        $('#pay-button').on('click', function (e) {
            e.preventDefault();

            let button = $(this);
            button.prop('disabled', true).text('Memproses...');

            $.ajax({
                url: "/checkout",
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    amount: 100000,
                    product_id: 123,
                    customer_id: 456,
                    quantity: 1,
                    total_price: 100000,
                    name: 'Jesse',
                    email: 'jesse@test.com',
                    phone_number: '081234567890'
                },
                success: function (response) {
                    console.log('Snap token:', response.snap_token);

                    window.snap.pay(response.snap_token, {
                        onSuccess: function (result) {
                            alert('Pembayaran berhasil!');
                            console.log(result);
                            button.prop('disabled', false).text('Bayar Sekarang');
                        },
                        onPending: function (result) {
                            alert('Menunggu pembayaran...');
                            console.log(result);
                            button.prop('disabled', false).text('Bayar Sekarang');
                        },
                        onError: function (result) {
                            alert('Terjadi error pembayaran!');
                            console.log(result);
                            button.prop('disabled', false).text('Bayar Sekarang');
                        },
                        onClose: function () {
                            alert('Popup ditutup tanpa menyelesaikan pembayaran');
                            button.prop('disabled', false).text('Bayar Sekarang');
                        }
                    });
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal membuat transaksi');
                    button.prop('disabled', false).text('Bayar Sekarang');
                }
            });
        });
    </script>
</body>
</html>
