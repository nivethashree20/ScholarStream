@echo off
echo Starting ScholarStream Local Server...
echo Keep this black window OPEN! Do not close it!
echo.
echo Once this says "Development Server started", go to http://127.0.0.1:8000 in your browser.
echo.
"C:\xampp\php\php.exe" -S 127.0.0.1:8000 -t "c:\xampp\htdocs\mini"
pause
