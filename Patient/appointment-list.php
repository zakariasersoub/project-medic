<!doctype html>
<html lang="en">
  <head>
    <link rel="preload" href="important-image.webp" as="image">

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="Appointment.css" />
    <title>Appointments Dashboard</title>
  </head>
<div class="appointments-list">
<?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $badgeClass = ($row['status'] == "scheduled") ? "pending" :
                        (($row['status'] == "completed") ? "confirmed" : "cancelled");

            $formattedDate = date("l, F j, Y", strtotime($row['request_time']));
            $formattedTime = date("g:i A", strtotime($row['request_time']));

            // Status badge color logic
            $statusColors = [
            'pending' => 'orange',
            'accepted' => 'green',
            'completed' => 'green',
            'confirmed' => 'green',
            'canceled' => 'red',
            'cancelled' => 'red'
            ];
            $status = strtolower($row['status']);
            $color = isset($statusColors[$status]) ? $statusColors[$status] : 'secondary';

            echo '
            <article class="appointment-card">
            <div class="appointment-details">
                <span class="status-badge ' . $badgeClass . '" style="background:' . $color . '; color:#fff;">' . ucfirst($row['status']) . '</span>
                <h3 class="doctor-name mt-2">' . htmlspecialchars($row['doctor_name']) . '</h3>
                <p class="appointment-type">' . htmlspecialchars($row['appointment_type']) . '</p>
                <div class="appointment-meta d-flex align-items-center">
                <div class="meta-item me-4 d-flex align-items-center">
                    <img src="images/callandry.svg" alt="Date" class="meta-icon" width="20" height="20" />
                    <span>' . $formattedDate . '</span>
                </div>
                <div class="meta-item d-flex align-items-center">
                    <img src="images/watch.svg" alt="Time" class="meta-icon" width="20" height="20" />
                    <span>' . $formattedTime . '</span>
                </div>
                </div>
            </div>
            <div class="appointment-actions mt-2">
                <a class="btn btn-danger btn-md me-2" style="font-size: 1.1rem; padding: 0.6rem 1.2rem;" href="cancel_appointment.php?id=' . $row['id'] . '">
                <img src="images/x.svg" alt="Cancel" class="action-icon" width="15" height="15" /> Cancel
                </a>
                <a class="btn btn-primary btn-md" style="font-size: 1.1rem; padding: 0.6rem 1.2rem;" href="reschedule_appointment.php?id=' . $row['id'] . '">
                <img src="images/return.svg" alt="Reschedule" class="action-icon" width="18" height="18" /> Reschedule
                </a>
            </div>
            </article>';
        }
    } else {
        echo "<p>No appointments found.</p>";
    }

    $conn->close();
?>
</div>