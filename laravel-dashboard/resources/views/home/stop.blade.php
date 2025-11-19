<meta name="csrf-token" content="{{ csrf_token() }}" />

<div class="container mt-5">
	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h4 class="mb-3">Manually Force Stop Charging</h4>
					<p class="text-muted">Enter your Transaction ID to request a force stop.</p>
					<div class="mb-3">
						<label for="transactionId" class="form-label">Transaction ID</label>
						<input type="text" id="transactionId" class="form-control" placeholder="e.g. ABCD">
					</div>
					<button id="forceStopBtn" class="btn btn-danger w-100">Force Stop</button>
					<div id="forceStopMsg" class="mt-3" style="display:none;"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
	const btn = document.getElementById('forceStopBtn');
	const input = document.getElementById('transactionId');
	const msg = document.getElementById('forceStopMsg');

	function showMessage(text, ok) {
		msg.style.display = 'block';
		msg.className = ok ? 'alert alert-success' : 'alert alert-danger';
		msg.textContent = text;
	}

	btn.addEventListener('click', function(){
		const val = (input.value || '').trim();
		if (!val) {
			showMessage('Please enter a valid Transaction ID.', false);
			return;
		}
		Swal.fire({
			title: 'Force Stop Charging?',
			text: 'Are you sure you want to stop this charging session?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Yes, stop it',
			cancelButtonText: 'Cancel'
		}).then(function(result){
			if (result.isConfirmed) {
				fetch("{{ route('zora.stop.action') }}", {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
					},
					body: JSON.stringify({ transactionId: val })
				}).then(async function(resp){
					const data = await resp.json().catch(() => ({}));
					if (resp.ok && data && data.ok) {
						showMessage('Request submitted. If valid, the session will be stopped shortly.', true);
						Swal.fire('Submitted', 'Force stop request submitted.', 'success');
					} else {
						showMessage('Failed to submit request. Please try again.', false);
						Swal.fire('Error', 'Failed to submit request.', 'error');
					}
				}).catch(function(){
					showMessage('Network error. Please try again.', false);
					Swal.fire('Error', 'Network error.', 'error');
				});
			}
		});
	});
});
</script>


