<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spider - Welcome</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Consolas">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Consolas', monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            position: relative;
        }

        img {
            width: 200px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-text {
            font-size: 5.5rem;
            text-shadow: 0 0 10px #fff;
            font-weight: 300;
        }

        h1 {
            font-size: 2rem;
            margin-top: 20px;
            font-weight: 300;
        }

        .button-container {
            margin-top: 30px;
            display: flex;
            gap: 20px;
        }

        .button {
            padding: 10px 20px;
            border: 2px solid #fff;
            border-radius: 50px;
            background: none;
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.3s, color 0.3s;
        }

        .button:hover {
            background: #fff;
            color: #000;
        }

        .footer-text {
            position: absolute;
            bottom: 10px;
            font-size: 0.9rem;
            text-align: center;
            width: 100%;
        }

        .version-text {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="spider.svg" alt="Spider Logo">
        <span class="logo-text">Spider</span>
    </div>
    <h1>Are you ready to weave your web?</h1>
    <div class="button-container">
        <a href="https://github.com/yeoblyv/spider" class="button">GitHub</a>
        <!-- <a href="https://spider.oblyvantsov.com" class="button">Documentation</a> -->
    </div>
    <div class="footer-text">Copyright © 2024 Yehor Oblyvantsov</div>
    <div class="version-text">Version <?php echo Spider::getVersion() . " (" . Spider::getCoreHash() . ")"; ?></div>
</body>

</html>