<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams and Players</title>
    <com:THead />
    <style>
        .game-item { margin: 6px 0; }
        .game-item > button { display: block; font-weight: bold; min-width: 220px; text-align: left; }
        .game-players { margin: 4px 0 8px 32px; padding-left: 12px; border-left: 3px solid #ccc; }
        .stat-item { margin: 2px 0; }
        .stat-item input[type=submit] { display: block; min-width: 200px; text-align: left; }
    </style>
</head>
<body>
    <com:TForm>
        <header>
            <h1>Game Stats</h1>
        </header>
        <com:TRepeater ID="GameList">
            <prop:HeaderTemplate>
                <div class="game-list-header"><h3>Games</h3></div>
            </prop:HeaderTemplate>

            <prop:ItemTemplate>
                <div class="game-item">
                    <button type="button" onclick="var p=document.getElementById('<%# $this->PlayersPanel->ClientID %>'); p.style.display = (p.style.display === 'none') ? 'block' : 'none';"><%# htmlspecialchars($this->Page->teamLabel($this->Data)) %></button>
                    <com:TActivePanel ID="PlayersPanel" CssClass="game-players" Style="display:none">
                        <com:TRepeater ID="PlayerList" DataSource="<%# $this->Data->game_stats %>">
                            <prop:ItemTemplate>
                                <div class="stat-item">
                                    <com:TActiveButton
                                        Text="<%# '#' . $this->Data->player->team->name . ' ' . $this->Data->player->first_name . ' ' . $this->Data->player->last_name  %>"
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