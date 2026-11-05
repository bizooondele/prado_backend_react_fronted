<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players</title>
    <com:THead />
    <style>
        .grid { border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #ccc; padding: 4px 10px; }
        .grid th a { text-decoration: none; }
    </style>
</head>
<body>
    <com:TForm>
        <header>
            <h1>Players</h1>
        </header>
        <p>
            Rows per page:
            <com:TActiveDropDownList ID="PageSize" OnSelectedIndexChanged="pageSizeChanged">
                <com:TListItem Value="10" Text="10" Selected="true" />
                <com:TListItem Value="25" Text="25" />
                <com:TListItem Value="50" Text="50" />
            </com:TActiveDropDownList>
        </p>
        <com:TActiveDataGrid ID="Grid"
            CssClass="grid"
            AutoGenerateColumns="false"
            AllowSorting="true"
            OnSortCommand="sortGrid"
            AllowPaging="true"
            PageSize="10"
            PagerStyle.Mode="Numeric"
            PagerStyle.PageButtonCount="10"
            OnPageIndexChanged="pageChanged"
            OnItemDataBound="itemDataBound">
            <com:TTemplateColumn>
                <prop:HeaderTemplate><com:TActiveCheckBox ID="HeaderCheck" OnCheckedChanged="Page.headerToggled" ToolTip="Check / uncheck all rows on all pages" /></prop:HeaderTemplate>
                <prop:ItemTemplate><com:TActiveCheckBox ID="RowCheck" Value="<%# $this->NamingContainer->Data['id'] %>" OnCheckedChanged="Page.rowToggled" /></prop:ItemTemplate>
            </com:TTemplateColumn>
            <com:TBoundColumn HeaderText="Team name" DataField="team_name" SortExpression="team_name" />
            <com:TBoundColumn HeaderText="First name" DataField="first_name" SortExpression="first_name" />
            <com:TBoundColumn HeaderText="Last name" DataField="last_name" SortExpression="last_name" />
            <com:TBoundColumn HeaderText="Position" DataField="position" SortExpression="position" />
            <com:TBoundColumn HeaderText="Jersey number" DataField="jersey_number" SortExpression="jersey_number" />
            <com:TBoundColumn HeaderText="Game stats (Points)" DataField="points" SortExpression="points" />
        </com:TActiveDataGrid>
    </com:TForm>
</body>
</html>
