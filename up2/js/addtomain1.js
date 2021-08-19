var modal_agree = document.getElementById('simpleModal');

var modalBtn = document.getElementById('modalBtn');

var closeBtn = document.getElementsByClassName('closeBtn')[0];

// modalBtn.addEventListener('click', openModal);

closeBtn.addEventListener('click', closeModal);

window.addEventListener('click', outsideClick);

function openModal() {
    modal_agree.style.display = "block";
}

function closeModal() {
    modal_agree.style.display = 'none';
}

function outsideClick(e) {
    // modal_agree.style.display = "block";
    

}
if(!window.localStorage.getItem('storge')){
    modal_agree.style.display = "block";
    window.localStorage.setItem('storge','true')
}