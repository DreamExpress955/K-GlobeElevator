/**
 * @file audio.cpp
 * @brief Implements elevator floor audio announcements.
 *
 * Provides the audio functionality used by the Service Controller.
 * The playFloor() function plays a different WAV audio file depending
 * on the elevator floor number supplied.
 */

#include "../include/audio.h"

/**
 * @brief Plays the audio announcement for a specified elevator floor.
 *
 * Selects and plays the WAV file associated with the supplied floor
 * number. The Linux aplay command is used to play the audio file.
 *
 * Supported floors:
 * - Floor 1 plays Funny_Floor_1.wav
 * - Floor 2 plays Funny_Floor_2.wav
 * - Floor 3 plays Funny_Floor_3.wav
 *
 * @param floornumber Elevator floor number to announce.
 *
 * @return void
 */
void playFloor(int floornumber)
{
    if (floornumber == 1)
    {
        std::system("aplay ../audio/Funny_Floor_1.wav");
    }
    else if (floornumber == 2)
    {
        std::system("aplay ../audio/Funny_Floor_2.wav");
    }
    else if (floornumber == 3)
    {
        std::system("aplay ../audio/Funny_Floor_3.wav");
    }
}