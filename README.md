ZenCartMaxMindMinFraud
======================

ZenCart MaxMind minFraud (e-Commerce Fraud Detection)

<p align="center"><font size="+2">MaxMind Credit Card Fraud Detection v1.4 for ZenCart 
</font></p>
<p align="left"><u><font size="+1">Overview</font></u><br />
1. This has been tested on ZenCart 1.3.9h, your mileage on earlier versions may vary.<br />
2. This addon will allow the store owner to query the MaxMind server to identify and place a risk score on transactions.<br />
3. You MUST have an account with MaxMind to use this addon! You may get a test license with 1000 queries at:<br />
<a href="http://www.maxmind.com/app/ccv2r_signup?rId=k3live">http://www.maxmind.com/app/ccv2r_signup</a></p>
<p align="left">4. From MaxMind <a href="http://www.maxmind.com/app/ccv_overview?rId=k3live">website</a>:<br />
  <strong>Fraudster techniques becoming more   advanced</strong><br />
Techniques used in online card-not-present fraud are becoming   more and more sophisticated. Traditional fraud screening tools can only   determine if a credit card is legitimate or if the user-entered account   information matches those on record. Today, fraudsters can obtain personal   credit card information, pose as the legitimate card holder, and bypass standard   fraud checks. </p>
<p><strong>Looking at fraud from a different angle</strong><br />
At   MaxMind, we approach fraud screening in a different way. We examine an online   transaction from various angles. Our tools are not geared towards verifying the   authenticity of the credit card details used for the purchase, but rather,   identifying if the purchaser is the legitimate card holder. Through our   analysis, we have been able to identify traits and patterns that are associated   with fraudulent orders. By asking the right questions, we can provide e-commerce   businesses with the necessary information to detect fraudulent orders before the   payment is processed. </p>
<p>5. This is what you should see once installed: </p>
<p align="left"><font size="+2"><img src="readme_files/orders.jpg" width="735" height="209" /></font> <br />
  <br />
<font size="+2"><img src="readme_files/detailed_orders.gif" width="861" height="243" /></font></p>
<p align="left"><u><font size="+1">License<br />
</font></u>This code is licensed  under the terms of the GNU Public License(GPL).</p>
<p align="left"><u><font size="+1">Warning</font></u><br />
BACKUP your database and files BEFORE installation. Use this contribution at YOUR OWN RISK.</p>
<p align="left"><u><font size="+1">Installation</font></u><br />
With Zen Magic only one file needs to be replaced IF you want credit card orders to auto-process when the order is placed:<br />
includes/modules/pages/checkout_process/header.php</p>
<p align="left">If you want to use the credit card issuer (BIN) name and phone number fraud scoring, or if you use the default credit card module and do not store credit card numbers on the server (you e-mail the middle numbers), you will need to replace:<br />
  includes/modules/payment/cc.php<br />
(This needs to be done to change which numbers stay on the server and which get e-mailed to you, we need the first 6 numbers to pass on to MaxMind)</p>
<p align="left">If you use GoogleCheckout you need to replace:<br />
  googlecheckout/responsehandler.php<br />
(This needs to be done so the ip address is collected from the GoogleCheckout message and stored in the database.)</p>
<p align="left">The core file includes/classes/order.php has to be modified to seperate the customers ip address. <br />
  (A regex smart person could rewrite the ip address grabbing code to remove this requirement.)<br />
</p>
<p align="left">All other files are added and used with Zen Magic:<br />
  admin/maxmind_orders.php<br />
admin/maxmind_update.php<br />
admin/images/flags/*.*
<br />
admin/includes/boxes/extra_boxes/maxmind_orders_customers_dhtml.php<br />
admin/includes/extra_datafiles/maxmind_orders.php<br />
admin/includes/languages/english/extra_definitions/maxmind_orders.php<br />
googlecheckout/responsehandler.php ***Only needed if you use GoogleCheckout***<br />
images/maxmind_ccfd.gif<br />
includes/extra_datafiles/maxmind_orders.php<br />
includes/languages/english/extra_definitions/maxmind_orders.php<br />
  includes/modules/maxmind/CreditCardFraudDetection.php<br />
  includes/modules/maxmind/GeoIP.dat<br />
  includes/modules/maxmind/geoip.inc<br />
  includes/modules/maxmind/HTTPBase.php<br />
  includes/modules/maxmind/maxmind.php<br />
  includes/modules/payment/cc.php ***Read note above, only needed if you want to use BIN scoring or e-mail partial credit card numbers*** <br />
includes/modules/pages/checkout_process/header.php ***Read note above, only needed if you want to auto-process orders***<br />
</p>
<p align="left">Once you have uploaded the required files you need to add the new database fields:<br />
Go to Your Store -&gt; Admin -&gt; Tools -&gt; Install SQL Patches<br />
  Browse and upload maxmind_orders.sql</p>
<p align="left">Make sure you have your MaxMind License Key (available from <a href="http://www.maxmind.com/app/ccv_overview?rId=k3live">http://www.maxmind.com/app/ccv2r_signup</a>):<br />
Go to Your Store -&gt; Admin -&gt; Configuration -&gt; My Store <br />
Find MaxMind License Key and update it with your license key. </p>
<p align="left"><u><font size="+1">Upgrading</font></u><font size="+1"><br />
</font>Replace all files.<br />
  Go to Your Store -&gt; Admin -&gt; Tools -&gt; Install SQL Patches<br />
Browse and upload maxmind_orders_1.1-1.2.sql</p>
<p align="left"><u><font size="+1">Change Notes 1.3.1 - 1.4</font></u><br />
1. Updated core files for ZC 1.3.9h<br />
2. Updated GeoIP API<br />
3. Updated GeoIP Data to January 2011
<br />
4. Updated MaxMind API to 1.49
</p>
<p align="left"><u><font size="+1">Change Notes 1.3 - 1.3.1</font></u><br />
  1. User_agent and accept_language cleanup (not yet implemented).<br />
2. ClientAPI cleanup.</p>
<p align="left"><u><font size="+1">Change Notes 1.2 - 1.3</font></u><br />
  1. MaxMind API Version Updated to 1.48 (Adds support for user_agent and accept_language).<br />
  2. Updated core files for ZC 1.3.8a.<br />
  3. Updated GoogleCheckout to 1.4.7.<br />
</p>
<p align="left"><u><font size="+1">Change Notes 1.1 - 1.2</font></u><br />
1. Fixed delivery city not being passed for processing.<br />
2. Fixed delivery state not being passed for processing.<br />
3. Now compatible with GoogleCheckout (Check to see if state &amp; country are already zone id's, store ip address).<br />
4. Implemented new scoring (0-100 vs 0-10).<br />
5. Implemented new score explanation.<br />
6. Now includes checkout definitions for BIN name and phone on order page.<br />
7. Manual update now shows what information is being sent out for processing.<br />
8. Manual update query type selectable link (basic, standard, premium).<br />
9. Manual delete now uses messageStack for success message.<br />
10. Credit card checkout now has language file for bin name and phone.<br />
</p>
<p align="left"><u><font size="+1">Change Notes 1.0 - 1.1</font></u><br />
  1. MaxMind API Version Updated to 1.43 (Adds support for username and password input fields).<br />
2. Corrected Standard and Premium services fields.<br />
3. Added link to MaxMind ID field to view input/ouput in MaxMind Support Log.<br />
4. Removed spam score, now integrated in returned proxy score.<br />
5. Implemented BIN name and phone in credit card module.<br />
6. Implemented score inaccuracy reporting, currently supports feedback for scores 1,3, &amp; 5.<br />
7. Fixed fraud score on order_edit page, no longer rounded.<br />
</p>
<p align="left"><u><font size="+1">To-Do List </font></u><br />
1. Implement new observer class notifier and remove modifications to checkout_success.php.<br />
2. Implement minFraud alert handling. (<a onclick="return top.js.OpenExtLink(window,event,this)" href="http://www.maxmind.com/app/alert_url?rId=k3live" target="_blank">http://www.maxmind.com/app/alert_url</a>) <br />
3. Implement order status change for score threshold.<br />
4. Consider adding user_agent and accept_language to query.<br />
5. JS check BIN name and phone format.<br />
6. Consider modifying PayPal DP to record required details to analyze order.<br />
7. Continue to harass Ajeh for putting starting and ending IP address in same field. </p>
<p align="left"><u><font size="+1">Bug Reports</font></u><br />
Please PM me on the ZenCart forum with any bug reports<br />
<a href="http://www.zen-cart.com/forum/private.php?do=newpm&amp;u=338">Quick PM Link</a></p>
<p align="left"><u><font size="+1">Support</font></u><br />
Visit the <a href="http://www.zen-cart.com/forum/showthread.php?t=55599">ZenCart forum board</a> to read the latest and get support.<br />
  If you cannot find your answer there, <a href="http://www.zen-cart.com/forum/private.php?do=newpm&amp;u=338">PM me</a>.<br />
</p>
<p align="left"><u><font size="+1">FAQs</font></u><br />
Q. When I run a manual update I see the correct information on the update page but the information is not displayed on the main order page.<br />
A. You probably already ran a manual update for that order and an entry in the db already exists. Hit the 'Delete MaxMind Information' link and then run another manual update.<br />
  <br />
  Q. This contribution doesn't work!<br />
A. It works for me :)</p>
<p align="left">Q. Can you do it for me?<br />
  A. I will help out as much as I can on the ZenCart forum board, beyond that if you PM me expect to hire me.</p>
<p align="left">Q. How can I contribute to the author?<br />
A. Please direct all contributions to the <a href="http://www.zen-cart.com/index.php?main_page=infopages&amp;pages_id=14">ZenCart Team</a></p>
<p align="left">Q. Why is the readme in HTML?<br />
  A. I like HTML, get over it :)</p>
<p align="left">Last modified January 25th, 2011<br />
  Kevin M. <br />
  ses707
</p>
