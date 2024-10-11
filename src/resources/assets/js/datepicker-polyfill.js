document.addEventListener('DOMContentLoaded', function(){
    var date = document.getElementById('complete_education');

    if(date){
        date.addEventListener('keydown', function(e){
            e.preventDefault();
        });
    }
});