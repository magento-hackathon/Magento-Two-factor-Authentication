var authForm = new VarienForm('twofactorauth-form', true);

Event.observe(window, 'load', function() {
    var toggleConfigureOptions = function() {
        if ($("twofactorauth-enabled").getValue() == 1) {
            $("twofactorauth-configure").show();
        } else {
            $("twofactorauth-configure").hide();
        }
    };

    Event.observe($("twofactorauth-enabled"), 'change', function() {
        toggleConfigureOptions();
    });

    toggleConfigureOptions();
});