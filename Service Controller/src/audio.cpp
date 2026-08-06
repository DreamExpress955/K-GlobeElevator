
#include "../include/audio.h"


void playFloor(int floornumber){
    if(floornumber == 1){
        std::system("aplay ../audio/Funny_Floor_1.wav");
    }
    else if(floornumber == 2){
        std::system("aplay ../audio/Funny_Floor_2.wav");
    }
    else if(floornumber == 3){
        std::system("aplay ../audio/Funny_Floor_3.wav");
    }
}