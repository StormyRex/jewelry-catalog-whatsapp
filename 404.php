<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — TP Jewellery</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Lato', sans-serif;
            background: #FDF6F0;
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            overflow: hidden;
        }
        body::before {
            content: ''; position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(232,180,184,0.35) 0%, transparent 70%);
            top: -150px; right: -150px;
            border-radius: 50%; pointer-events: none;
        }
        body::after {
            content: ''; position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,169,110,0.2) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            border-radius: 50%; pointer-events: none;
        }
        .box {
            position: relative; z-index: 1;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 24px;
            padding: 60px 50px;
            text-align: center;
            box-shadow: 0 8px 40px rgba(201,169,110,0.1);
            max-width: 480px; width: 90%;
        }
        .code {
            font-family: 'Cormorant Garamond', serif;
            font-size: 80px; font-weight: 600;
            color: #C9A96E; line-height: 1;
            margin-bottom: 8px;
        }
        .divider {
            width: 40px; height: 2px;
            background: linear-gradient(to right, #C9A96E, #E8B4B8);
            margin: 16px auto;
            border-radius: 2px;
        }
        h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px; color: #2C2C2C;
            margin-bottom: 10px;
        }
        p { font-size: 14px; color: #aaa; margin-bottom: 28px; line-height: 1.6; }
        a {
            display: inline-block;
            padding: 12px 28px;
            background: linear-gradient(135deg, #C9A96E, #d4b896);
            color: #fff; border-radius: 30px;
            text-decoration: none; font-weight: 700;
            font-size: 14px; letter-spacing: 0.5px;
            box-shadow: 0 4px 16px rgba(201,169,110,0.3);
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">404</div>
        <div class="divider"></div>
        <h2>Page Not Found</h2>
        <p>The product or page you're looking for doesn't exist or may have been removed.</p>
        <a href="index.php">← Back to Collection</a>
    </div>
</body>
</html>