console.log("recurly-manager-create-account");
recurly.configure("ewr1-T64Wy7XtowRhETZaifm6U8"); //  live
var recurlyElements = recurly.Elements();
var recurlyCardElement = recurlyElements.CardElement();
recurlyCardElement.attach("#recurly-elements");

var createAccountForm = document.querySelector("#createAccount");

// construct and attach your Elements...
createAccountForm.addEventListener("submit", function (event) {
  var form = this;
  event.preventDefault();
  recurly.token(recurlyElements, form, function (err, token) {
    if (err) {
      console.log("Error: ", err);
      sessionStorage.setItem("recurly_errors", err.message);
    } else {
      console.log("success");
      form.submit();
    }
  });
});
