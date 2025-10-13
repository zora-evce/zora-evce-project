<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Test Stop Transaction</title>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link href="{{ asset('templates/sb/css/styles.css') }}" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            body { padding: 24px; }
            .card { max-width: 480px; margin: 40px auto; padding: 16px; background: #023c61; color: #fff; border-radius: 8px; }
            label { display: block; margin-bottom: 8px; }
            input[type="text"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
            button { margin-top: 12px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h3>Remote Stop Transaction</h3>
            <label for="transactionId">Transaction ID</label>
            <input type="text" id="transactionId" placeholder="Enter transactionId" />
            <button id="stopBtn" class="btn btn-primary btn-md">Stop</button>
        </div>

        <script>
            $(document).on('click', '#stopBtn', function(){
                var id = $('#transactionId').val().trim();
                if (!id) { alert('Please input a transactionId'); return; }

                var $btn = $(this);
                $btn.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: 'https://zora.apenable.com/api/ocpp/commands',
                    method: 'POST',
                    headers: { 'X-OCPP-Key': 'ZORA_SUPER_SECRET' },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        station_code: 'Zora1',
                        connector: 1,
                        command: 'RemoteStopTransaction',
                        payload: { transactionId: id }
                    })
                }).done(function(resp){
                    alert('Stop command sent.');
                }).fail(function(xhr){
                    alert('Failed to send stop command');
                }).always(function(){
                    $btn.prop('disabled', false).text('Stop');
                });
            });
        </script>
    </body>
    </html>


