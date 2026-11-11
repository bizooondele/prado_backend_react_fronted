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
            <com:TRepeater ID="UserList" OnItemCreated="onUserItemCreated" OnItemCommand="viewUserDetails">
                <prop:HeaderTemplate>
                    <div class="user-list-header">
                        <h3>Active Users</h3>
                    </div>
                </prop:HeaderTemplate>
                
                <prop:ItemTemplate>
                    <div class="user-item">
                        <com:TLabel Text="<%# $this->Data['name'] %>" CssClass="user-name" />
                        <com:TLabel Text="<%# $this->Data['email'] %>" CssClass="user-email" />
                        <com:TActiveButton 
                            Text="View Details"
                            CommandName="view"
                            CommandParameter="<%# $this->Data['id'] %>" />
                    </div>
                </prop:ItemTemplate>
                
                <prop:FooterTemplate>
                    <div class="user-list-footer">
                        Total: <%# $this->Parent->DataKeys->Count %> users
                    </div>
                </prop:FooterTemplate>
            </com:TRepeater>
            <com:TActiveLabel ID="UserDetails" />
        </com:TForm>
</body>
</html>