<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Clock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        #clock {
            font-size: 3em;
        }
    </style>
</head>
<body>

<div id="clock"></div>
<audio id="notificationSound">
<source src="../inc/sons/notification.mp3" type="audio/mp3">
    Votre navigateur ne prend pas en charge l'élément audio.
  </audio>

<button class="btn btn-success" onclick="livrerCommande()">Livrer</button>





<script>
    // Function to update the clock
    function updateClock() {
        // Create a new Date object
        var now = new Date();

        // Get the current time components
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();

        // Add leading zero if needed
        hours = (hours < 10) ? '0' + hours : hours;
        minutes = (minutes < 10) ? '0' + minutes : minutes;
        seconds = (seconds < 10) ? '0' + seconds : seconds;

        // Display the time
        document.getElementById('clock').innerHTML = hours + ':' + minutes + ':' + seconds;

        // Update every second
        setTimeout(updateClock, 1000);
    }

    // Initial call to start the clock
    updateClock();
</script>

<script>
    function livrerCommande() {
      // Code pour marquer la commande comme livrée

      // Jouer le son de notification
      playNotificationSound();
    }

    function playNotificationSound() {
      var audio = document.getElementById('notificationSound');
      audio.play();
    }
  </script>

</body>
</html>
