$(".sidebar ul li").on('click', function () {
    $(".sidebar ul li.active").removeClass('active');
    $(this).addClass('active');
});

$('.open-btn').on('click', function () {
    $('.sidebar').addClass('active');

});

$('.close-btn').on('click', function () {
    $('.sidebar').removeClass('active');

})


//other js na dgd

// Function to handle zooming in
function zoomIn() {
    const container = document.querySelector('.container');
    container.style.transform = 'scale(0.9)';
  }
  
  // Function to handle zooming out
  function zoomOut() {
    const container = document.querySelector('.container');
    container.style.transform = 'scale(0.7)';
  }

  function checkMediaQuery(mediaQuery) {
    if (mediaQuery.matches) {
        zoomIn();
    } else {
        zoomOut();
    }
  }
  const mediaQuery = window.matchMedia('(min-width: 768px)');
  
  // Call checkMediaQuery function at initial load
  checkMediaQuery(mediaQuery);
  mediaQuery.addListener(checkMediaQuery);
  

  function togglePasswordVisibility(fieldId, otherFieldId, icon) {
    var field = document.getElementById(fieldId);
    var otherField = document.getElementById(otherFieldId);

    if (isNaN(field.value)) {
        field.setCustomValidity("Please enter a valid number.");
        field.reportValidity();
        return;
    }

    if (field.type === "password") {
        field.type = "text";
        otherField.type = "text";
        icon.innerHTML = '<i class="fas fa-eye"></i>';
    } else {
        field.type = "password";
        otherField.type = "password";
        icon.innerHTML = '<i class="fas fa-eye-slash"></i>';
    }
}


const pin3 = document.getElementById('ip3');
pin3.addEventListener('input', enforceSixDigitPin);

const pin4 = document.getElementById('ip4');
pin4.addEventListener('input', enforceSixDigitPin);

function enforceSixDigitPin(event) {
    let pin = event.target.value;
    pin = pin.replace(/\D/g, '');
    if (pin.length > 6) {
        event.target.value = pin.slice(0, 6);
    }
}

document.getElementById('container').style.display = 'none';

    // Get the button and modal container for registartionn 
    const registerBtn = document.getElementById('Register-btn');
    const modalContainer = document.getElementById('container');

    registerBtn.addEventListener('click', function () {
        modalContainer.style.display = 'block';
    });
    document.getElementById('closebtnAdmin').addEventListener('click', function () {
        modalContainer.style.display = 'none';
    });

    function hideRegistrationForm() {
        var registrationForm = document.getElementById('container');
        registrationForm.style.display = 'none';
    }




    function validatePin() {
        var pinField = document.getElementById('ip3');
        var confirmPinField = document.getElementById('ip4');

        if (pinField.value !== confirmPinField.value) {
            pinField.setCustomValidity("PINs don't match");
            confirmPinField.setCustomValidity("PINs don't match");
            return false;
        } else {
            pinField.setCustomValidity("");
            confirmPinField.setCustomValidity("");
            return true;
        }
    }
