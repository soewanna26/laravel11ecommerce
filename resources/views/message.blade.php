@if (Session::has('success'))
    <div class="alert alert-success" id="successMessage">
        <p class="mb-0 pb-0">{{ Session::get('success') }}</p>
    </div>
@endif

@if (Session::has('error'))
    <div class="alert alert-danger" id="errorMessage">
        <p class="mb-0 pb-0">{{ Session::get('error') }}</p>
    </div>
@endif

<script>
    setTimeout(function() {
        var successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 5000);

    setTimeout(function() {
        var errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 5000);
</script>
