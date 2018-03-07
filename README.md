# OptimizeAnySite
Php script for optimize any existing site code

***

How to optimize

***

1. Rename your site's current index.php to index-orig.php
2. Extract this archive to your site's root directory
3. Go to your site and check its source code to see what's happened

***

Demo

***

You can see Before.jpg and After.jpg with Google Page Speed Insights results. 
I got it on Wordpress site with installed WooCommerce and BuddyPress component.

***

The following steps, you must do manually for increase site speed:

*** Leverage browser caching:
  
	You need to add the following code into .htaccess file in site's root folder.  

		<IfModule mod_expires.c>  
			ExpiresActive On  
			ExpiresByType image/jpg "access 1 year"  
			ExpiresByType image/jpeg "access 1 year"  
			ExpiresByType image/gif "access 1 year"  
			ExpiresByType image/png "access 1 year"  
			ExpiresByType text/css "access 1 month"  
			ExpiresByType application/pdf "access 1 month"  
			ExpiresByType application/javascript "access 1 month"  
 			ExpiresByType application/x-javascript "access 1 month"  
			ExpiresByType application/x-shockwave-flash "access 1 month"  
			ExpiresByType image/x-icon "access 1 year"  
			ExpiresDefault "access 2 days"  
		</IfModule>  
  
*** Server response time  
	Enable System Debug or Profiler, then check profiler for get slow extensions.  
	If needed, enable caching for get speed.  
	If it still slow, then, seems your hosting provider server are slow or huge loaded.  
  	
*** Serve static content from a cookieless domain (optional)  
	Create a subdomain using cPanel for the static content  
