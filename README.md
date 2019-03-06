Contao DCA Notifications
========================

This extension allows to send notifications in the Contao backend for any database based data container using the 
[Notification Center].

Requirements
------------

 - Contao `^4.4`
 - PHP `>= 7.1`
 - Symfony `^3.3 || ^4.0`
 
Install
-------

You can install `hofff/contao-dca-notification` with Composer / Contao Manager.

Usage
-----

 - Create a new notification of the type *DCA Submit Notification* and select the table.
 - A new legend *Notification* is created in the data container edit view
 - Check checkbox *Send Notification* and select the created notification
 
Tokens
------

All fields being available in the active record of the data container are available as tokens.

 - **label_\*** The label of a field
 - **raw_\*** The raw value of a field
 - **value_\***  The parsed value of a field
 - **admin_email** The email of the admin
 
Limitations
-----------

 - This extension auto creates new columns for defined notifications but it does not delete it if the notifications are 
   changed/deleted. This behaviour is designedly so that no configured notifications would be deleted accidentally. Use
   the install tool to clean up old columns instead.
 - Though this extension is designed for a generic use case it should work with third party extensions. If not feel free
   to provide an [pull request] to achieve compatibility.  

[Notification Center]: https://github.com/terminal42/contao-notification_center
[pull request]: https://github.com/hofff/contao-dca-notification/pulls
