$( document ).ready(function() {
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

    $(document).on("change", "#foulsCount", function (e){
        alert(e.is(':checked'));
    })

    $(document).on("click", "#startGameButton", function (e){
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
            url: 'game',
            method: 'get',
            data: {
                // members:Object.assign(membersArray),
                // settings:Object.assign(settingArray)
            },
            success: function(response) {
            }
        });
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
