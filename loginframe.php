<!DOCTYPE html>
<html ng-app='dmmLogin' ng-controller="mainCtrl">
  <head>
    <title>DMM Login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="force-rendering" content="webkit">  
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">

    <!--  favicon  -->
    <link rel="apple-touch-icon" sizes="57x57" href="/asset/favicon/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/asset/favicon/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/asset/favicon/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/asset/favicon/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/asset/favicon/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/asset/favicon/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/asset/favicon/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/asset/favicon/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/asset/favicon/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="/asset/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/asset/favicon/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="/asset/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/asset/favicon/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/asset/favicon/manifest.json">
    <link rel="shortcut icon" href="/asset/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#2b95df">
    <meta name="msapplication-TileImage" content="/asset/favicon/mstile-144x144.png">
    <meta name="theme-color" content="#2b95df">

    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="http://cdn.bootcss.com/angular.js/1.4.7/angular.min.js"></script>
    <script src="http://cdn.bootcss.com/angular-loading-bar/0.8.0/loading-bar.min.js"></script>
    <link href="http://cdn.bootcss.com/angular-loading-bar/0.8.0/loading-bar.min.css" rel="stylesheet" media="none" onload="media = 'all'">
    <link href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" media="none" onload="media = 'all'">
    <link href="/asset/style/default.css" rel="stylesheet" media="none" onload="media = 'all'">
  </head>
  <body class="noto bg">
    <div class="container-fluid" role="main">
      <div class="login-form">
        <h2>DMM Login</h2>
        <div class="alert alert-danger" role="alert" ng-show="error"><span ng-bind="error"></span></div>
        <input type="email" class="form-control" placeholder="DMM ID" ng-model="login_data.email" autofocus>
        <input type="password" class="form-control" placeholder="Password" ng-model="login_data.password">
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me" ng-model="login_data.remember"> Remember me
          </label>
        </div>
        <!--div class="radio">
          <label>
            <input type="radio" name="loadType" value="iframe" ng-model="login_data.loadType">
            return iframe url
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="loadType" value="redirect" ng-model="login_data.loadType">
            redirect
          </label>
        </div-->
        <button class="btn btn-lg btn-primary btn-block" type="submit" ng-click="login()">Sign in</button>
        <button class="btn btn-lg btn-success btn-block" type="submit" ng-show="hasCookie" ng-click="login(true)">Use cookie</button>
      </div>
    </div>
    <script src="/asset/js/app.js"></script>
  </body>
</html>
