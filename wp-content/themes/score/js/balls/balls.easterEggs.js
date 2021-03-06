!(function (balls){
balls.easterEggs = {
  commandList:[],
  init:function(){
    this.events();
  },
  events:function(){
    $("body").on("keyup", function(e){
      var code = e.which,
          commandList = balls.easterEggs.commandList;
      
      switch(code){
        case 38: // up
          if(commandList.length < 2){
            if(commandList.length === 1 && commandList[0] === 38){
              commandList.push(38);
            }else{
              commandList.push(38);
            }
          }else{ commandList = []; }
          break;
        case 40: // down
          if(commandList.length < 4 && commandList.length > 1){
            if(commandList.length === 2 && commandList[1] === 38){
              commandList.push(40);
            }else if(commandList[2] === 40){
              commandList.push(40);
            }
          }else{ commandList = []; }
          break;
        case 37: // left
          if(commandList.length === 4 && commandList[3] === 40){
            commandList.push(37);
          }else if(commandList.length === 6 && commandList[5] === 39){
            commandList.push(37);
          }else{ commandList = []; }
          break;
        case 39: // right
          if(commandList.length === 5 && commandList[4] === 37){
            commandList.push(39);
          }else if(commandList.length === 7 && commandList[6] === 37){
            commandList.push(39);
          }else{ commandList = []; }
          break;
        case 66: // 'B' or 'b'
          if(commandList.length === 8 && commandList[7] === 39){
            commandList.push(66);
          }else{ commandList = []; }
          break;
        case 65: // 'A' or 'a'
          if(commandList.length === 9 && commandList[8] === 66){
            commandList.push(65);
          }else{ commandList = []; }
          break;
        case 13: // enter
          if(commandList.length === 10 && commandList[9] === 65){
            // yay konomi code
            commandList.push(13);
            balls.easterEggs.konamiCode(commandList);
          }else{ commandList = []; }
          break;
        default:
          commandList = [];
          return false;
      }

      return false;
    });
  },
  konamiCode:function(codeArry){
    // should double check array but to lazy right now
    toast.success("Good job, here is 30 lives now get out there and blast some aliens!");
    this.commandList = [];
  }
}
})(window.balls);
