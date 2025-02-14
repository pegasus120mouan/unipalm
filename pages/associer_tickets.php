<?php
require_once '../inc/functions/connexion.php';

// Récupérer le numéro du bordereau depuis l'URL
$numero_bordereau = isset($_GET['bordereau']) ? $_GET['bordereau'] : '';

// Récupérer les IDs des tickets sélectionnés
$selected_tickets = isset($_GET['tickets']) ? (array)$_GET['tickets'] : [];

// Traiter la mise à jour si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tickets']) && !empty($_POST['bordereau'])) {
    $conn = getConnexion();
    $tickets = $_POST['tickets'];
    $numero_bordereau = $_POST['bordereau'];
    
    try {
        $conn->beginTransaction();
        
        foreach ($tickets as $id_ticket) {
            $stmt = $conn->prepare("UPDATE tickets SET numero_bordereau = ? WHERE id_ticket = ?");
            $stmt->execute([$numero_bordereau, $id_ticket]);
        }
        
        $conn->commit();
        header("Location: bordereaux.php?success=1");
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

// Debug - Afficher les données reçues
echo "<!-- Debug: ";
var_dump($_GET);
echo " -->";

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Association des tickets</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <div class="content-wrapper" style="margin-left: 0;">
            <section class="content-header">
                <div class="container-fluid">
                    <h1>Association des tickets</h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bordereau n° <?= htmlspecialchars($numero_bordereau) ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)) : ?>
                                <div class="alert alert-danger">
                                    <h5><i class="icon fas fa-ban"></i> Erreur</h5>
                                    <p><?= htmlspecialchars($error) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($selected_tickets)) : ?>
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Tickets sélectionnés (<?= count($selected_tickets) ?>) :</h5>
                                    <ul>
                                        <?php foreach ($selected_tickets as $ticket_id) : ?>
                                            <li>ID Ticket : <?= htmlspecialchars($ticket_id) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <form method="post" action="update_tickets_bordereau.php">
                                    <input type="hidden" name="bordereau" value="<?= htmlspecialchars($numero_bordereau) ?>">
                                    <?php foreach ($selected_tickets as $ticket_id) : ?>
                                        <input type="hidden" name="tickets[]" value="<?= htmlspecialchars($ticket_id) ?>">
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Confirmer l'association
                                    </button>
                                </form>
                            <?php else : ?>
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Attention</h5>
                                    <p>Aucun ticket n'a été sélectionné.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="bordereaux.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour aux bordereaux
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');
                
                // Désactiver le bouton pendant la soumission
                $submitBtn.prop('disabled', true);
                
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'bordereaux.php?success=1';
                        } else {
                            alert('Erreur : ' + (response.error || 'Une erreur est survenue'));
                            $submitBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('XHR:', xhr);
                        console.error('Status:', status);
                        console.error('Error:', error);
                        
                        var errorMessage = 'Erreur de communication avec le serveur';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = 'Erreur : ' + xhr.responseJSON.error;
                        }
                        alert(errorMessage);
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
