var inputEl = document.getElementById('years_of_practice');

if(inputEl){
    var goodKey = '0123456789';
    var key = null;

    var checkInputTel = function() {
        var start = this.selectionStart,
            end = this.selectionEnd;

        var filtered = this.value.split('').filter(filterInput);
        this.value = filtered.join("");

        /* Prevents moving the pointer for a bad character */
        var move = (filterInput(String.fromCharCode(key)) || (key == 0 || key == 8)) ? 0 : 1;
        this.setSelectionRange(start - move, end - move);
    }

    var filterInput = function(val) {
        return (goodKey.indexOf(val) > -1);
    }

    /* This function save the character typed */
    var res = function(e) {
        console.log(e);
        key = (typeof e.which == "number") ? e.which : e.keyCode;
    }

    inputEl.addEventListener('input', checkInputTel);
    inputEl.addEventListener('keypress', res);
    inputEl.addEventListener('touchstart', res);
}
