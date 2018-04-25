# BP Reply By Email - Simple New Topic Email Address #

This is a companion plugin for [BP Reply By Email](https://github.com/r-a-y/bp-reply-by-email) (RBE), allowing BuddyPress group members to create new forum topics with a simpler email address.

This plugin was developed for the [CUNY Academic Commons](http://commons.gc.cuny.edu).  Licensed under the GPLv2 or later.

#### What does this plugin do?

RBE has built-in support to allow members of a BuddyPress group to create new bbPress forum topics via email.  However, the new topic email address is comprised of a hexadecimal hash, which is (intentionally) hard to remember. For example, `foobar+d41d8cd98f00b204e9800998ecf8427e-new@gmail.com` (if you're using IMAP mode in RBE) or `d41d8cd98f00b204e9800998ecf8427e-new@reply.example.com` (if you're using Inbound mode in RBE).

This plugin changes the group's new topic email address to use the following format - `group-test-group@reply.example.com` - where `test-group` is the group slug.  Group administrators can further customize the `test-group` portion of the email address on the group's "Manage > Details" page.

Requirements
-
* BP Reply By Email (with [Inbound mode enabled](https://github.com/r-a-y/bp-reply-by-email/wiki/Starter-Guide#1-inbound-email-mode) or with IMAP mode).  Requires v1.0-RC6 or higher.
* BuddyPress (with the Groups component activated)
* bbPress (with [BuddyPress group support enabled](https://codex.buddypress.org/getting-started/installing-group-and-sitewide-forums/#b-set-up-group-and-sitewide-forums))
* PHP 5.3+

How to use?
-
1. Fulfill the requirements listed above.
2. In BP Reply By Email:
 - If you're already using Inbound Mode, you can skip this section.
 - If you're using IMAP Mode, you have two options:
    1. Switch RBE to use [Inbound Mode](https://github.com/r-a-y/bp-reply-by-email/wiki/Starter-Guide#1-inbound-email-mode).
    2. Configure RBE to use [Inbound Mode](https://github.com/r-a-y/bp-reply-by-email/wiki/Starter-Guide#1-inbound-email-mode) and switch back to using IMAP mode.  What this will do is IMAP mode will be used for replies and Inbound mode will be used for new group forum topics.
3. Activate this plugin.
4. Navigate to a BuddyPress group with a bbPress forum attached to it (eg. `example.com/groups/test-group/forum/`).
    - Scroll down to the bottom and you will see the "Post New Topics via Email" as usual.
    - Click on "Find out how"
    - The group's new topic email address should now use the simpler version now - `group-test-group@reply.example.com`.<br />
  ![Screenshot of a group's Forum page](https://cloud.githubusercontent.com/assets/505921/18261097/d00e0748-73a8-11e6-91db-e3ddb02c8bfa.png)
5. Now, send an email to this email address to test that it is working properly.  (Make sure you are a member of the group before sending the email!)
6. (optional) To customize the group's new topic email address, navigate to `Manage > Details` and alter the email address to your liking.
