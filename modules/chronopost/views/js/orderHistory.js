$(document).ready(function(){
   var trackingNumber = $('#order-detail .box table tbody tr td:nth-child(5)');
   var d = document.createElement('div');
   d.innerHTML = trackingNumber.text();
   trackingNumber.text('');
   trackingNumber.append(d.firstElementChild);
});
