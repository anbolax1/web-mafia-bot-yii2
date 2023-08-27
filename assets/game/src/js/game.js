$( document ).ready(function() {
    var gameInterval;
    var gameTimeSpan = $("h2#gameTime>span");
    var gameSeconds = $("div#techFields>#gameSeconds").text();
    gameInterval = setInterval(function () {
        gameSeconds++;
        var h = Math.floor(gameSeconds / 3600);
        var m = Math.floor(gameSeconds % 3600 / 60);
        var s = Math.floor(gameSeconds % 3600 % 60);

        h = h.toLocaleString('en-US', {
            minimumIntegerDigits: 2,
            useGrouping: false
        });
        m = m.toLocaleString('en-US', {
            minimumIntegerDigits: 2,
            useGrouping: false
        });
        s = s.toLocaleString('en-US', {
            minimumIntegerDigits: 2,
            useGrouping: false
        });
        $(gameTimeSpan).text(h + ':' + m + ':' + s);
    }, 1000);

    $(document).on("click", "#shuffleMembersButton", function (e){
        let membersList = $("ol#membersList");
        let membersArray = [];
        let members = $("li.member");

        $(members).each(function (index){
            let discord_id = $(this).find($("p#member")).attr('discord_id');
            let avatar = $(this).find($("img.avatar")).attr('src');
            let name = $(this).find($('span#name')).text();
            membersArray[index] = {id: discord_id, name: name, avatar: avatar};
        })

        $(membersList).empty();

        //перемешиваем 3 раза
        membersArray = shuffleArray(membersArray);
        membersArray = shuffleArray(membersArray);
        membersArray = shuffleArray(membersArray);

        membersArray.forEach(function (item){
            let membersList = $("ol#membersList");

            let img = $("<img>",{
                'src': item.avatar,
                'alt': 'Avatar',
                'class': 'avatar'
            });
            let span = $("<span>", {
                'id':'name',
                'text': item.name
            });
            let p = $("<p>", {
                'id': 'member',
                'discord_id': item.id
            }).append(img).append(span);

            let memberLi = $("<li>", {
                'class': 'member',
            }).append(p);

            $(membersList).append(memberLi)
        })
    })

    $(document).on("click", "span#deleteMember", function (e){
        let p = $(this).parent($('p#member'));
        let li = $(p).parent($("li"));
        if($(li).hasClass('member')){
            $(li).removeClass('member');
            $(p).find($("span#name")).css('opacity', '0.3');
            $(p).find($("span#deleteMember")).text('');
        } else {
            $(li).addClass('member');
            $(p).find($("span#name")).css('opacity', '1');
            $(p).find($("span#deleteMember")).text('X');
        }
    })

    $(document).on("click", "#updateMembersButton", function (e){
        //TODO сделать обновление участников без перезагрузки страницы
        location.reload();
    })

    $(document).on("click", "#startGameButton", function (e){
        $("#startGameSpinner").show();
        let membersArray = [];
        let settingArray = [];
        let members = $("li.member");

        $(members).each(function (index){
            let discord_id = $(this).find($("p#member")).attr('discord_id');
            let avatar = $(this).find($("img.avatar")).attr('src');
            let name = $(this).find($('span#name')).text();
            membersArray[index] = {discord_id: discord_id, name: name, avatar: avatar};
        });

        let inputs = $("input.custom-switch");
        $(inputs).each(function (index){
            let id = $(this).attr('id');
            let value = $(this).is(':checked');
            settingArray[index] = {id: id, value: value};
        });

        jQuery.ajax({
            url: 'create-game',
            method: 'post',
            data: {
                members:Object.assign(membersArray),
                settings:Object.assign(settingArray)
            },
            success: function(response) {
                $("#startGameSpinner").hide();
                window.location.replace("game");
            }
        });
    })

    var int;
    $(document).on("click", "span.seconds", function (e){
        clearInterval(int);
        let id = $(this).attr('id');
        let timerDisplay = $("span#timerDisplay");

        // let seconds = $(this).attr('id');
        let seconds = timerDisplay.text();

        if($(this).hasClass('set-seconds')){
            $(timerDisplay).text($(this).attr('id'));
            seconds = parseInt($(timerDisplay).text());

        } else if ($(this).hasClass('plus-seconds')){
            seconds = parseInt(seconds) + parseInt($(this).attr('id'));
        }

        int = setInterval(function () {
            if (seconds > 0) {
                seconds--;
                $(timerDisplay).text(seconds);
            }
        }, 1000);
    })

    $(document).on("click", "span.action-with-timer", function (e){
        clearInterval(int);
        /*if($(this).attr('id') === 'pause') {
           clearInterval(int);
       } else */
        if($(this).attr('id') === 'continue') {
           let timerDisplay = $("span#timerDisplay");
           let seconds = timerDisplay.text();
           int = setInterval(function () {
               if (seconds > 0) {
                   seconds--;
                   $(timerDisplay).text(seconds);
               }
           }, 1000);
       }
    })

    $(document).on("click", "span#hideGameRulesButton", function (e){
        let gameRulesBlock = $("div#gameRulesBlock");
        if(gameRulesBlock.hasClass('hidden')){
            $("span#hideGameRulesButton").text('>');
            $("div#gameInfoBlock").css('width', '73%').css('transition', '0.3s');
            gameRulesBlock.css('transition', '0.3s').css('width', '25%').css('opacity', '1').removeClass('hidden').show();
        } else {
            $("span#hideGameRulesButton").text('<');
            $("div#gameInfoBlock").css('width', '100%').css('transition', '0.3s');
            gameRulesBlock.css('transition', '0.3s').css('width', '0').css('opacity', '').addClass('hidden').hide();

        }
    })

    $(document).on("click", "span.member-name", function (e){
        let slot = $(this).find($("span.member-slot")).attr('slot');
        if($(this).hasClass('on-vote')){
            $(this).removeClass('on-vote');
            $(`.memberOnVote#${slot}`).remove();
        } else {
            $(this).addClass('on-vote');
            $("span#membersOnVoteSpan")
                .append($("<span>", {
                    'class': 'memberOnVote',
                    'id': slot,
                    'text': slot
                }))
        }

        if($("span.memberOnVote").length > 7) {
            $("span#membersOnVoteSpan").css('font-size', '2.3vw');
        } else {
            $("span#membersOnVoteSpan").css('font-size', '3vw');
        }
    })

    $(document).on("click", "span#clearMembersOnVote", function (e){
        $("span#membersOnVoteSpan").empty();
        $("span.member-name").removeClass('on-vote');
        clearInterval(int);
        $("span#timerDisplay").text('');
    })

    $(document).on("click", "span.foul", function (e){
        let currentFoul = $(e.currentTarget);
        let currentFoulList = $(currentFoul).parent($("span.fouls-list")).find($("span.foul"));
        let foulNumber = $(this).text();

        if($(currentFoul).hasClass('on-vote')){
            $(currentFoulList).each(function (index){
                if(index+1 >= foulNumber) {
                    $(this).removeClass('on-vote');
                }
            })
        } else {
            $(currentFoulList).each(function (index){
                if(index+1 <= foulNumber) {
                    $(this).addClass('on-vote');
                }
            })
        }
    })

    $(document).on("click", "span#plus30, span.the-best-move-member", function (e){
        if($(e.currentTarget).hasClass('on-vote')){
            $(e.currentTarget).removeClass('on-vote');
        } else {
            $(e.currentTarget).addClass('on-vote');
        }
    })

    $(document).on("click", "span.delete-member", function (e){
        let modal = $("#deleteMemberReasonModal");

        $(modal).find($('div#techFieldsModal>span')).text();

        let currentFoul = $(e.currentTarget);

        let gameId = $("div#techFields>span#gameId").text();
        let memberDiscordId = $(e.currentTarget).parent($("p.member-row")).attr('discord_id');
        let foulsCount = $(e.currentTarget).parent($("p.member-row")).find($('span.foul.on-vote')).length;

        $("div#techFieldsModal>span#gameId").text(gameId);
        $("div#techFieldsModal>span#memberDiscordId").text(memberDiscordId);
        $("div#techFieldsModal>span#foulsCount").text(foulsCount);

        let killedMembersCount = $(`p.member-row[reason="killed"]`).length;
        if(killedMembersCount === 0) {
            $(modal).find($("span#killed"))
                .attr('data-bs-target', '#theBestMoveModal')
                .attr('data-bs-toggle', 'modal')
                .attr('data-bs-dismiss', 'modal');
        } else {
            $(modal).find($("span#killed"))
                .removeAttr('data-bs-target')
                .removeAttr('data-bs-toggle')
                .removeAttr('data-bs-dismiss');
        }

        let memberRow = $(`p.member-row[discord_id="${memberDiscordId}"]`);
        if($(memberRow).hasClass('opacity03')){
            $(memberRow).removeClass('opacity03');
            $(memberRow).removeAttr('reason');

            jQuery.ajax({
                url: 'delete-member-from-game',
                method: 'post',
                data: {
                    gameId:gameId,
                    memberDiscordId:memberDiscordId,
                    toDelete:'false',
                },
                success: function(response) {
                    let result = response;
                    if(typeof result['message'] != 'undefined'){
                        alert(result['message']);
                    }
                }
            });
        } else {
            modal.modal('show');
        }
    })

    $(document).on("click", "div.modal-buttons#deleteMemberReasonModal > span", function (e){
        let modal = $("#deleteMemberReasonModal");

        let deleteReason = $(e.currentTarget).attr('id');

        let gameId = $(modal).find($("span#gameId")).text();
        let memberDiscordId = $(modal).find($("span#memberDiscordId")).text();
        let foulsCount = $(modal).find($("span#foulsCount")).text();

        $("div#techFieldsModal>span#gameId").text(gameId);
        $("div#techFieldsModal>span#memberDiscordId").text(memberDiscordId);
        $("div#techFieldsModal>span#foulsCount").text(foulsCount);

        let memberRow = $(`p.member-row[discord_id="${memberDiscordId}"]`);
        let toDelete;

        let killedFirst = false;
        let killedMembersCount = $(`p.member-row[reason="killed"]`).length;
        if(killedMembersCount === 0 && deleteReason === 'killed') {
            killedFirst = true;
        }

        $(memberRow).attr('reason', deleteReason);

        //если уже удалён, возвращаем
        if($(memberRow).hasClass('opacity03')){
            $(memberRow).removeClass('opacity03');
            toDelete = false;
        } else {
            $(memberRow).addClass('opacity03');
            toDelete = true;
            $(memberRow).attr('reason', deleteReason);
        }
        modal.modal('hide');


        jQuery.ajax({
            url: 'delete-member-from-game',
            method: 'post',
            data: {
                gameId:gameId,
                memberDiscordId:memberDiscordId,
                foulsCount:foulsCount,
                deleteReason:deleteReason,
                toDelete:toDelete,
                killedFirst:killedFirst,
            },
            success: function(response) {
                let result = response;
                if(typeof result['message'] != 'undefined'){
                    alert(result['message']);
                }
            }
        });
    })

    $(document).on("click", "span#sendTheBestMove", function (e){
        let modal = $("#deleteMemberReasonModal");
        let theBestMoveModal = $("#theBestMoveModal");

        let gameId = $(modal).find($("span#gameId")).text();
        let memberDiscordId = $(modal).find($("span#memberDiscordId")).text();

        theBestMoveModal.modal('hide');

        let theBestMoveSlots = [];

        $("span.the-best-move-member.on-vote").each(function (index) {
            theBestMoveSlots.push($(this).attr('id'));
        });

        $("span.the-best-move-member").removeClass('on-vote');
        jQuery.ajax({
            url: 'write-the-best-move',
            method: 'post',
            data: {
                gameId:gameId,
                memberDiscordId:memberDiscordId,
                theBestMoveSlots:Object.assign(theBestMoveSlots),
            },
            success: function(response) {
                let result = response;
                if(typeof result['message'] != 'undefined'){
                    alert(result['message']);
                }
            }
        });
    })

    $(document).on("click", "h2#finishGameButton", function (e){
        let modal = $("#finishGameModal");
        modal.modal('show');
    })

    $(document).on("click", "span.finish-game", function (e){
        $(e.currentTarget).find("#finishGameSpinner").show();
        let modal = $("#finishGameModal");
        let gameId = $("div#techFields>span#gameId").text();
        let finishType = $(e.currentTarget).attr('id');

        jQuery.ajax({
            url: 'finish-game',
            method: 'post',
            data: {
                gameId:gameId,
                finishType:finishType,
            },
            success: function(response) {
                let result = response;
                if(typeof result['message'] != 'undefined'){
                    alert(result['message']);
                    return;
                }
                let page = finishType === 'canceled' ? 'starting' : 'finish';
                $(e.currentTarget).find("#finishGameSpinner").hide();
                window.location.replace(page);
            }
        });
    })

    $(document).on("click", "#startNewGameButton", function (e){
        $(e.currentTarget).addClass('progress-bar-striped progress-bar-animated');
        window.location.replace('starting');
    })

    $(document).on("click", "#showPrioritiesButton", function (e){
        let modal = $("#prioritiesModal");
        modal.modal('show');
    })
});

function shuffleArray(array) {
    for (var i = array.length - 1; i > 0; i--) {

        // Generate random number
        var j = Math.floor(Math.random() * (i + 1));

        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }

    return array;
}
