<HTML>
    <head>
        <style type="text/css">
            body {
                text-align: center;
                font-family: Helvetica, Arial, sans-serif;
            }

            header {
                text-align: center;
                font-size: 75px;
                text-shadow: 2px 2px rgba(400, 0, 0, 0.6);
            }

            .content {
                color: black;
                font-size: 50px;
                text-shadow: 2px 2px rgba(400, 0, 0, 0.6);
            }

            h2 {
                font-size: 24px;
                font-style:italic;
                font-weight: normal;
            }

            .content .time-loading {
                font-size: 50px;
                font-family: Helvetica, Arial, sans-serif;
            }
        </style>
    </head>
    <body>
        <header>
            Clawbot 5000 - CHAMPIONSHIP!
        </header>
        <h2>"Cooking up the best picks since 2012"</h2>
        <div class="content">
            <div class="time-loading">
                <?=$elapsedTimeString?>
            </div>
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                window.location.reload(true);
            }, 2500);
        </script>
    </body>
</HTML>