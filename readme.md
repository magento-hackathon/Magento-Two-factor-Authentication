Magento: Two-Factor-Authentication
=====================

----------

Magento Worldwide Online Hackathon, Januar 2014

----------

Implementation of an two-factor-authentication using Google's 2-Step Verification algorithm.

So admins can choose to login with additional authentication using the 2FA or a password from a one-time-password-list.

> **NOTE:**
> Default login will be also required to login!
> 2FA is only an additional login to increase the security.

Todo:
-
- Implement Google's 2FA-Algorithm and One-Time-Passwords
  - Table containing one-time-password's and 2FA-Data
- Disable/Enable for every Admin-User
- Integrate Google QR-Code-Generator
`http://www.google.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth://totp/idontplaydarts?secret=SECRETVALUEHERE`

