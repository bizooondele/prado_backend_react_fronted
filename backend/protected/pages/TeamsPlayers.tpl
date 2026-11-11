<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams and Players</title>
    <com:THead />
    <style>
        .team-item { margin: 6px 0; }
        .team-item > button { display: block; font-weight: bold; min-width: 220px; text-align: left; }
        .team-players { margin: 4px 0 8px 32px; padding-left: 12px; border-left: 3px solid #ccc; }
        .player-item { margin: 2px 0; }
        .player-item input[type=submit] { display: block; min-width: 200px; text-align: left; }
    </style>
</head>
<body>
    <com:TForm>
        <header>
            <h1>Teams and Players</h1>
        </header>
        <com:TRepeater ID="TeamList">
            <prop:HeaderTemplate>
                <div class="team-list-header"><h3>Teams</h3></div>
            </prop:HeaderTemplate>

            <prop:ItemTemplate>
                <div class="team-item">
                    <button type="button" onclick="var p=document.getElementById('<%# $this->PlayersPanel->ClientID %>'); p.style.display = (p.style.display === 'none') ? 'block' : 'none';"><%# htmlspecialchars($this->Page->teamLabel($this->Data)) %></button>
                    <com:TActivePanel ID="PlayersPanel" CssClass="team-players" Style="display:none">
                        <com:TRepeater ID="PlayerList" DataSource="<%# $this->Data->players %>">
                            <prop:ItemTemplate>
                                <div class="player-item">
                                    <com:TActiveButton
                                        Text="<%# '#' . $this->Data->jersey_number . ' ' . $this->Data->first_name . ' ' . $this->Data->last_name %>"
                                        CommandParameter="<%# $this->Data->id %>"
                                        OnCallback="Page.showPlayerDetails" />
                                </div>
                            </prop:ItemTemplate>
                        </com:TRepeater>
                    </com:TActivePanel>
                </div>
            </prop:ItemTemplate>
        </com:TRepeater>

        <h3>Player details</h3>
        <com:TActiveLabel ID="PlayerDetails" />
    </com:TForm>
</body>
</html>