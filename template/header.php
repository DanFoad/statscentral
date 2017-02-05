<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Stats Central</title>

    <!-- Meta Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimal-ui" />
    <meta name="description" content="INSERT DESCRIPTION HERE" />

    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/css/foundation.min.css" />
    <link rel="stylesheet" href="/css/materialize.css" />
    <link rel="stylesheet" href="/css/tablesaw.css" />
    <link rel="stylesheet" href="/css/style.css" />

    <!-- Favicon/App icons -->
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png" />
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/favicon-194x194.png" sizes="194x194" />
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192" />
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16" />
    <link rel="manifest" href="/manifest.json" />
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#2B3A4D" />
    <meta name="msapplication-TileColor" content="#2B3A4D" />
    <meta name="msapplication-TileImage" content="/mstile-144x144.png" />
    <meta name="theme-color" content="#2B3A4D" />

    <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->

    <script src="/js/vendor/jquery.min.js"></script>
    <script src="/js/materialize.js"></script>

</head>
<body>

    <header>
        <!-- <img class="header__logo" src="/img/logo/sc.png" alt="" /> -->
        <ul id="dropdown1" class="dropdown-content">
            <li><a href="/champions/">All Years</a></li>
            <li><a href="/champions/2015/">2015</a></li>
            <li><a href="/champions/2016/">2016</a></li>
        </ul>

        <ul id="dropdown2" class="dropdown-content">
            <li><a href="/players/">All Years</a></li>
            <li><a href="/players/2015/">2015</a></li>
            <li><a href="/players/2016/">2016</a></li>
        </ul>

        <nav>
            <div class="nav-wrapper">
              <a href="/" class="brand-logo"><img src="/img/icons/Stats-Central-Logo-White-Transparent.png" style="width: 190px; padding-left: 5%"></a>
              <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons">menu</i></a>
              <ul class="right hide-on-med-and-down">
                <li><a class="dropdown-button" href="#" data-activates="dropdown1">Champions<i class="material-icons right" style="line-height: 64px; margin-left: 0px; font-size: 1.2em">arrow_drop_down</i></a></li>
                <li><a class="dropdown-button" href="#" data-activates="dropdown2">Players<i class="material-icons right" style="line-height: 64px; margin-left: 0px; font-size: 1.2em">arrow_drop_down</i></a></li>
                <li><a href="/events/">Events</a></li>
                <li><a href="#">Teams</a></li>
                <li><a href="http://www.twitter.com/statscentralgg" target="_blank"><img src="/img/icons/twitter.png" style="width: 26px"></a></li>
<!--                 <li><a href="collapsible.html">Javascript</a></li>
                <li><a href="mobile.html">Mobile</a></li> -->
              </ul>
              <ul class="side-nav" id="mobile-demo">
                <li class="no-padding">
                    <ul class="collapsible collapsible-accordian">
                        <li class="bold">
                            <a href="/" class="collapsible-header">Home</a>
                        </li>
                    </ul>
                </li>
                <li class="no-padding">
                    <ul class="collapsible collapsible-accordian">
                        <li class="bold">
                            <a class="collapsible-header">Champions</a>
                            <div class="collapsible-body" style="display: none;">
                                <ul>
                                    <li>
                                        <a href="/champions/">All Years</a>
                                    </li>
                                    <li>
                                        <a href="/champions/2015/">2015</a>
                                    </li>
                                    <li>
                                        <a href="/champions/2016/">2016</a>
                                    </li>
                                </ul>
                            </div>

                        </li>
                    </ul>
                </li>
                <li class="no-padding">
                    <ul class="collapsible collapsible-accordian">
                        <li class="bold">
                            <a class="collapsible-header">Players</a>
                            <div class="collapsible-body" style="display: none;">
                                <ul>
                                    <li>
                                        <a href="/players/">All Years</a>
                                    </li>
                                    <li>
                                        <a href="/players/2015/">2015</a>
                                    </li>
                                    <li>
                                        <a href="/players/2016/">2016</a>
                                    </li>
                                </ul>
                            </div>

                        </li>
                    </ul>
                </li>
                <li class="no-padding">
                    <ul class="collapsible collapsible-accordian">
                        <li class="bold">
                            <a href="/events" class="collapsible-header">Events</a>
                        </li>
                    </ul>
                </li>
                <li class="no-padding">
                    <ul class="collapsible collapsible-accordian">
                        <li class="bold">
                            <a href="http://www.twitter.com/statscentralgg" class="collapsible-header">Twitter</a>
                        </li>
                    </ul>
                </li>
            </div>
            <script type="text/javascript">
                $(".button-collapse").sideNav();
            </script>
        </nav>

        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-73843101-1', 'auto');
          ga('send', 'pageview');

        </script>

    </header>
