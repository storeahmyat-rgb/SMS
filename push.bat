@echo off
setlocal
echo ==========================================================
echo SMS PROJECT GITHUB PUSH TOOL (AUTO-CONFIG)
echo ==========================================================
echo.

:: Check for Git
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Git is not installed.
    pause
    exit /b
)

:: Auto-Config Git (To avoid 'Identity' error)
echo Configuring Git Identity...
git config user.email "musaddiq@example.com"
git config user.name "Musaddiq Iqbal"

:: Initialize Git if not already
if not exist ".git" (
    echo Initializing local repository...
    git init
)

:: Remote Setup
echo Checking remotes...
git remote remove origin >nul 2>&1
git remote add origin https://github.com/muhammadahmer1999/SMS-Pro-Enhanced.git

:: Add and Commit
echo Adding files...
git add .
echo Committing changes...
git commit -m "Final Final Update: All dual-system fixes applied."

:: Detect branch
set BRANCH=master
for /f "tokens=*" %%i in ('git symbolic-ref --short HEAD 2^>nul') do set BRANCH=%%i

echo.
echo ----------------------------------------------------------
echo NOW ATTEMPTING TO PUSH TO %BRANCH%...
echo ----------------------------------------------------------
echo.
git push -u origin %BRANCH%

if %errorlevel% neq 0 (
    echo.
    echo [TRYING MAIN...]
    git push -u origin main
)

echo.
echo If it asked for login and you finished it, check your GitHub!
pause
