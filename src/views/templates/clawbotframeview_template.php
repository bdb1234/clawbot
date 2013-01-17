<HTML>
    <head>
        <style type="text/css">
            .content {
                font-size: 16px;
                font-family: Helvetica, Arial, sans-serif;
            }

            header {
                text-align: center;
                font-size: 75px;
                text-shadow: 2px 2px rgba(400, 0, 0, 0.6);
            }

            .lineup-view {
                float: left;
                width: 50%;
            }

            .draft-trend-item.picked {
                color: #4CA23C;
            }

            .draft-trend-item.great-pick {
                font-size: 30px;
                color: #E90A0A;
            }

            .draft-trend-item.good-pick {
                font-size: 26px;
                color: #F76D11;
            }

            .draft-trend-item.decent-pick {
                font-size: 22px;
                color: #30C73C;
            }
        </style>
    </head>
    <body>
        <header>
            Clawbot 5000 - CHAMPIONSHIP!
        </header>
        <div class="content">
        <?php foreach ($viewArray as $contentXhtml) { ?>
            <?=$contentXhtml?>
        <?php } ?>
        </div>
    </body>
</HTML>