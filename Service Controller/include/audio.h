/**
 * @file
 * @brief Declares audio functions used by the elevator Service Controller.
 *
 * Provides the function used by the Service Controller to play
 * floor-related audio based on the supplied floor number.
 */

#ifndef SERVICE_CONTROLLER_H
#define SERVICE_CONTROLLER_H

#include <iostream>
#include <cstdlib>

/**
 * @brief Plays the audio associated with an elevator floor.
 *
 * Uses the supplied floor number to determine which floor audio
 * should be played by the Service Controller.
 *
 * @param floornumber The elevator floor number to announce.
 *
 * @return void
 */
void playFloor(int floornumber);

#endif