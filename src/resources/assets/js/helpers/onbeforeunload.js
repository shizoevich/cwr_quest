export default function(e) {
    let confirmation = 'Leave site?';
    (e || window.event).returnValue = '';
    
    return confirmation;
}