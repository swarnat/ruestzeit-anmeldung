
## 3.1.0 - TBD

### After update:
  - Please check settings and set imprint in admin UI!
  - Define PRIVACYAGREEMENT_URL in .env.local of environment of docker container

### Feature
  - add feature to configure imprint for web and mail in admin panel
  - add newsletter function to inform registered persons / member
  - add tracking of mails sent over newsletter function
  - add tracking of opened file links in mails send over newsletter function
  - implement imprint configuration
  - allow configuration of url, where privacy agreement is shown

### Fix
  - hide real S3 URL for flyer and cache file within local application to reduce S3 traffic costs
  - UI of user editing improved
  - only ADMIN role can create new users and edit imprint

## 3.0.4 - 2025-04-14

### Fix
  - Set Prepayment done and final payment done in ListView do not clear custom fields anymore

## 3.0.3 - 2025-03-25

### Feature
  - Add option to delete done tours / registrations

### UI
  - Allow activation from Waitlist with one click

### Fix
  - Catch error, when no tour is existing

## 3.0.2 - 2025-03-25

### Bugfixes
  - Disable Doctrine Cache to prevent problems in admin

## 3.0.1 - 2025-03-15

### Bugfixes
  - Change Captcha field from "email_repeat" to "subject" to prevent autofill of android phones trigger spamcheck
  - UI changes in administration for repsonsive view
  
## 3.0.0 - 2025-02-27

### New features
  - Implementation of sharing access to tours entries
  - Implementation of Custom Fields to request during registration, related to a tours
  - List of registrations in administration is completely configurable
  - S3 Connection for Uploads, which are uploaded automatically in this version
  - A tour use a canonical domain, which will improve SEO handling

## 2.0.0 - 2024-12-15

### New features
  
  - Option to support multiple tours
  - Option to use custom labels for input fields
  - Add room type request, room mate request, referer
