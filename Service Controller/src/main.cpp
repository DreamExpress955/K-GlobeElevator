/**
 * @file main.cpp
 * @brief Main program for the K-Globe Elevator Service Controller.
 *
 * Provides the main menu and operating modes for the elevator
 * Service Controller.
 *
 * The Service Controller communicates with the Elevator Controller
 * through the CAN network and communicates with the Elevator database
 * to synchronize floor information and process website requests.
 *
 * Available operating modes include database monitoring, demo mode,
 * and the K-Globe multithreaded CAN communication mode.
 */

#include "../include/pcanFunctions.h"
#include "../include/databaseFunctions.h"
#include "../include/mainFunctions.h"
#include "../include/pcanFunctions_multithreaded.h"

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <iostream>
#include <libpcan.h>
#include <thread>

using namespace std;

/**
 * @brief External elevator state variable.
 *
 * This variable is declared in another source file and is available
 * to the Service Controller.
 */
extern int elev2;


/**
 * @brief Entry point for the K-Globe Elevator Service Controller.
 *
 * Displays the Service Controller menu and executes the operating mode
 * selected by the user.
 *
 * The available functionality includes:
 * - Listening for commands from the website.
 * - Running an automatic floor-to-floor demonstration.
 * - Running K-Globe mode with multithreaded CAN reception.
 * - Exiting the Service Controller.
 *
 * Some original manual CAN transmit and receive modes are currently
 * commented out but have been preserved for reference.
 *
 * @return 0 when the Service Controller exits normally.
 */
int main()
{
    int choice;

    // Variables used by the original manual CAN modes.
    // int ID;
    // int data;
    // int numRx;

    // Track the current and previous elevator floor.
    int floorNumber = 1;
    int prev_floorNumber = 1;

    // Continue displaying the Service Controller menu until the user exits.
    while (1)
    {
        // Clear the terminal before displaying the menu.
        system("@cls||clear");

        // Get the user's menu selection.
        choice = menu();

        switch (choice)
        {
            case 1:

                /*
                 * Original manual CAN transmission mode.
                 *
                 * This code is currently disabled but has been
                 * preserved for reference.
                 */

                /*
                ID = chooseID();

                data = chooseMsg();

                pcanTx(
                    ID,
                    data
                );

                db_setFloorNum(
                    FloorFromHex(data)
                );

                break;
                */

            case 2:

                /*
                 * Original manual CAN receive mode.
                 *
                 * This code is currently disabled but has been
                 * preserved for reference.
                 */

                /*
                printf(
                    "\nHow many messages to receive? "
                );

                scanf(
                    "%d",
                    &numRx
                );

                pcanRx(numRx);

                break;
                */

            case 3:

                /*
                 * Website command mode.
                 *
                 * The Service Controller polls the Elevator database
                 * once every second and checks whether the current floor
                 * has changed.
                 *
                 * When a change is detected, the corresponding floor
                 * command is transmitted to the Elevator Controller
                 * through the CAN network.
                 */

                printf(
                    "\nNow listening to commands from the website "
                    "- press ctrl-z to cancel\n"
                );

                /*
                 * Synchronize the database and elevator.
                 *
                 * The system begins with the database indicating
                 * that the elevator is on floor 1.
                 */

                // pcanTx(
                //     ID_SC_TO_EC,
                //     GO_TO_FLOOR1
                // );

                db_setFloorNum(1);

                while (1)
                {
                    // Get the current floor stored in the database.
                    floorNumber = db_getFloorNum();

                    /*
                     * If the database floor changes, send the
                     * corresponding floor command through CAN.
                     */
                    if (prev_floorNumber != floorNumber)
                    {
                        pcanTx(
                            ID_SC_TO_EC,
                            HexFromFloor(floorNumber),
                            "From Database"
                        );
                    }

                    // Save the current floor for the next comparison.
                    prev_floorNumber = floorNumber;

                    /*
                     * Poll the database once every second for
                     * a change in the floor number.
                     */
                    sleep(1);
                }

                break;


            case 4:

                /*
                 * Demo mode.
                 *
                 * Automatically moves the elevator between Floors
                 * 1, 2, and 3. The database is updated after each
                 * CAN floor command.
                 */

                printf(
                    "\nDemo Mode - loop from floor to floor "
                    "- press ctrl-z to cancel\n"
                );

                while (1)
                {
                    // Request Floor 1.
                    pcanTx(
                        ID_SC_TO_EC,
                        GO_TO_FLOOR1,
                        "Demo: Floor 1"
                    );

                    db_setFloorNum(1);

                    // Wait 20 seconds before the next floor request.
                    sleep(20);


                    // Request Floor 2.
                    pcanTx(
                        ID_SC_TO_EC,
                        GO_TO_FLOOR2,
                        "Demo: Floor 2"
                    );

                    db_setFloorNum(2);

                    // Wait 20 seconds before the next floor request.
                    sleep(20);


                    // Request Floor 3.
                    pcanTx(
                        ID_SC_TO_EC,
                        GO_TO_FLOOR3,
                        "Demo: Floor 3"
                    );

                    db_setFloorNum(3);

                    // Wait 20 seconds before restarting the cycle.
                    sleep(20);
                }

                break;


            case 5:

                /*
                 * Exit the Service Controller.
                 */
                return 0;


            case 6:
            {
                /*
                 * K-Globe operating mode.
                 *
                 * Starts multithreaded CAN reception while allowing
                 * the Service Controller to transmit CAN commands.
                 */

                printf(
                    "\nK-Globe's Mode - Receive and Transmit\n"
                );


                /*
                 * Create a separate thread responsible for receiving
                 * and processing CAN messages.
                 */
                std::thread canThread(
                    []()
                    {
                        pcanRxWithDetailsMultithreaded();
                    }
                );


                /*
                 * Send an initial command telling the Elevator
                 * Controller to return to Floor 1.
                 */
                int result = pcanTx(
                    ID_SC_TO_EC,
                    GO_TO_FLOOR1,
                    "Initial CAN transmission (reset to floor 1)"
                );


                // Check whether the initial CAN transmission failed.
                if (result != 0)
                {
                    printf(
                        "Initial CAN tranmission failed: 0x%x\n",
                        result
                    );
                }


                // Synchronize the database with the initial floor.
                db_setFloorNum(1);


                /*
                 * Wait for the CAN receive thread to complete
                 * before leaving K-Globe mode.
                 */
                canThread.join();

                break;
            }


            default:

                // Handle an invalid menu selection.
                printf(
                    "Error on input values"
                );

                sleep(3);

                break;
        }


        // Delay briefly before returning to the menu.
        sleep(1);
    }

    return 0;
}