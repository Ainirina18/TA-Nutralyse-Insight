@if(session('login_error'))
<div 
    class="modal-overlay"
    x-data="{ show: true }"
    x-show="show"
    x-transition
>
    <div class="modal-card">

        <!-- ICON -->
        <div class="modal-icon">
            <img src="{{ asset('images/error-login.png') }}" alt="error">
        </div>

        <!-- TITLE -->
        <h2 class="modal-title">
            OH NO! LOGIN GAGAL
        </h2>

        <!-- MESSAGE -->
        <p class="modal-text">
            {{ session('login_error') }}
        </p>

        <!-- BUTTON -->
        <button class="modal-btn" @click="show = false">
            OK
        </button>

    </div>
</div>
@endif