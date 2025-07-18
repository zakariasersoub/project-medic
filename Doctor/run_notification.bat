@echo off
:loop
php c:\xampp\htdocs\medic\Doctor\notification.php
timeout /t 60 >nul
goto loop