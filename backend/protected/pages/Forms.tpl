<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prado Application Demo</title>
    <com:THead />
</head>
<body>
    <com:TForm>
        <header>
            <h1>Building with Prado Framework</h1>
        </header>
        
        <main>
            <p>This page demonstrates Prado's component-based architecture.</p>
        </main>
        <div class="datetime-widget">
            <com:TActiveLabel 
                ID="DateTimeDisplay" 
                Text="Click the button to load current time"
                CssClass="display-time" />
            
            <com:TActiveButton 
                ID="UpdateButton"
                Text="Refresh Time" 
                OnClick="updateDateTime"
                CssClass="btn-update" />
                
            <com:TActiveButton
                ID="ServerTimeButton"
                Text="Get Server Time"
                OnCallback="loadServerTime"
                CssClass="btn-server" />
        </div>
        <com:StatusCard 
            Title="Database Connection"
            Status="success"
            Message="Connected to MySQL server successfully" />

        <com:StatusCard 
            Title="Cache System"
            Status="warning"
            Message="Cache warming in progress" />

        <com:StatusCard 
            Title="API Status"
            Status="error"
            Message="API service is currently unavailable" />
    </com:TForm>
</body>
</html>