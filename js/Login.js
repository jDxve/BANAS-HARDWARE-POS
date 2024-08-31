function togglePasswordVisibility(inputId, icon) {
    const inputField = document.getElementById(inputId);
    const iconElement = icon.querySelector('i');

    if (isNaN(inputField.value)) {
        inputField.setCustomValidity("Please enter a valid number.");
        inputField.reportValidity();
        return;
    }

    if (inputField.type === "password") {
        inputField.type = "text";
        iconElement.classList.remove("fa-eye-slash");
        iconElement.classList.add("fa-eye");
    } else {
        inputField.type = "password";
        iconElement.classList.remove("fa-eye");
        iconElement.classList.add("fa-eye-slash");
    }
}


function enforceSixDigitPin(event) {
    let pin = event.target.value;
    pin = pin.replace(/\D/g, '');
    if (pin.length > 6) {
        event.target.value = pin.slice(0, 6);
    }
}

const adminCodeInput = document.getElementById('Login-pin');
adminCodeInput.addEventListener('input', enforceSixDigitPin);

