<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .image-card {
            border: 1px solid #ccc; 
            box-shadow: 3px 3px 5px 2px whitesmoke; 
            margin: 10px; 
            padding: 10px; 
            border-radius:10px;
        }
        .animation {
            opacity: 0;
            transform: translateX(-100%);
            animation: slideIn 0.1s ease forwards;
        }
    
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
    <title>Cards</title>
</head>
<body>
<div class="container-fluid">
    <div class="row no-gutters">
        <?php $delay = 0; foreach($cards as $card){ ?>
        <div class="col-md-3 col-lg-2 col-sm-4 col-6">
            <div class="image-card animation" style="animation-delay: <?= $delay ?>s;">
                <div class="d-flex justify-content-center">
                    <img src="<?= base_url('Assets/cards/'.$card->cards) ?>" alt="<?= $card->cards ?>">
                </div>
                <div class='text-center mt-2'>
                    <span>Name: <?= $card->cards ?></span><br>
                    <span>ID: <?= $card->id ?></span>
                </div>
            </div>
        </div>
        <?php $delay += 0.1; } ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
