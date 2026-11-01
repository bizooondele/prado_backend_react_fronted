<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stadiums</title>
    <com:THead />
    <style>
        .stadium-item { margin: 12px 0; }
        .stadium-teams { margin: 4px 0 8px 24px; }
        .team-item { margin: 2px 0; }
    </style>
</head>
<body>
    <com:TForm>
        <header>
            <h1>Stadiums and Teams</h1>
        </header>
        <com:TRepeater ID="StadiumList">
            <prop:HeaderTemplate>
                <h3>Stadiums</h3>
            </prop:HeaderTemplate>

            <prop:ItemTemplate>
                <section class="stadium-item">
                    <h4><%# htmlspecialchars($this->Data->name) %></h4>
                    <div>
                        <%# htmlspecialchars($this->Data->city . ', ' . $this->Data->state) %>
                        | Capacity: <%# htmlspecialchars((string) $this->Data->capacity) %>
                    </div>
                    <div class="stadium-teams">
                        <strong>Teams</strong>
                        <com:TRepeater ID="TeamList" DataSource="<%# $this->Data->teams %>">
                            <prop:ItemTemplate>
                                <div class="team-item">
                                    <%# htmlspecialchars($this->Data->city . ' ' . $this->Data->name) %>
                                </div>
                            </prop:ItemTemplate>
                        </com:TRepeater>
                    </div>
                </section>
            </prop:ItemTemplate>
        </com:TRepeater>
    </com:TForm>
</body>
</html>
