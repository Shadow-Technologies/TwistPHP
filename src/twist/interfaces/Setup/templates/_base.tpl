<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	    <title>Setup | TwistPHP</title>
	    <!--================================ META ================================-->
	    <meta charset="utf-8">
		<meta name="robots" content="none">
	    <!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

	    <!--================================ THIRD-PARTY RESOURCES ================================-->
		{core:unsemantic}
		{core:font-awesome}
		{core:arable}

	    <!--================================ CSS ================================-->
	    <link href="{route:interface_uri}/resources/css/base.css" type="text/css" rel="stylesheet">

	    <!--================================ JAVASCRIPT ================================-->
		<!--<script src="{route:interface_uri}/resources/js/base.js"></script>-->

	    <!--================================ LINKED DOCUMENTS ================================-->
	    <link rel="shortcut icon" type="image/x-icon" href="{core:logo-favicon}">

	    <!--================================ MOBILE STUFF ================================-->
	    <meta name="apple-mobile-web-app-capable" content="yes">
	    <meta name="apple-mobile-web-app-status-bar-style" content="black">
	    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
	</head>
	<body>
	    <div class="grid-container">
	    <div class="grid-100 tablet-grid-100 mobile-grid-100">
	        <h1 class="no-top-margin"><img src="{route:interface_uri}/resources/images/logo.png">TwistPHP Setup</h1>
	    </div>
	    <ul class="tabs">
	        {element:components/tabs.php}
	    </ul>
	    <div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-90 mobile-prefix-5">
	        {route:response}
	        <div class="clear"></div>
	    </div>
	    </div>
	</body>
</html>