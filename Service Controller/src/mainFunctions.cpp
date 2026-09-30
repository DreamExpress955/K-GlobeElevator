/**
 * @file mainFunctions.cpp
 * @brief Implements menu and conversion functions for the elevator Service Controller.
 *
 * Provides the user menus used by the Service Controller to select
 * operating modes, CAN message IDs, and elevator floor commands.
 *
 * This file also contains utility functions for converting between
 * elevator floor numbers and the hexadecimal CAN command values used
 * by the K-Globe Elevator system.
 */

#include "../include/pcanFunctions.h"
#include "../include/mainFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <stdlib.h>
#include <unistd.h>
#include <libpcan.h>


/**
 * @brief Displays the main Service Controller menu.
 *
 * Allows the user to select one of six Service Controller operating
 * modes. The function continues prompting until a valid selection
 * between 1 and 6 is entered.
 *
 * Menu options include:
 * - 1: Transmit a CAN message.
 * - 2: Receive CAN messages.
 * - 3: Control the elevator from the website.
 * - 4: Run the elevator demo mode.
 * - 5: Exit the program.
 * - 6: Run the K-Globe receive/transmit mode.
 *
 * @return The menu option selected by the user.
 */
int menu()
{
    int usrchoice = 0;

    system("@cls||clear");

    while (1)
    {
        printf("\n\nMenu - Transmit/Receive CAN Messages\n");
        printf("1. Transmit CAN message using this program\n");
        printf("2. Receive CAN message(s) using this program\n");
        printf("3. Control elevator from website\n");
        printf("4. Demo mode - loop\n");
        printf("5. Exit program\n");
        printf("6, K-Globe's program\n");

        printf("\nYour choice: ");

        scanf(
            "%d",
            &usrchoice
        );

        // Return the user's selection when it is valid.
        if (usrchoice >= 1 && usrchoice <= 6)
        {
            return usrchoice;
        }
        else
        {
            // Notify the user and redisplay the menu.
            printf(
                "\nPLEASE SELECT FROM CHOICES 1-6 ONLY!\n\n"
            );

            sleep(3);

            system("@cls||clear");
        }
    }
}


/**
 * @brief Allows the user to select a CAN message ID.
 *
 * Displays a menu containing the CAN communication paths used by the
 * elevator network. The selected menu item is converted into the
 * corresponding CAN message ID.
 *
 * Available CAN IDs include communication between the Supervisory
 * Controller, Elevator Controller, Car Controller, and each floor
 * controller.
 *
 * @return The CAN message ID associated with the user's selection.
 */
int chooseID()
{
    int IdChoice = 0;   // Menu item selected by the user.
    int IDvalue = 0;    // Corresponding CAN ID value.

    while (1)
    {
        system("@cls||clear");

        printf(
            "\nChoose sender and receiver for message\n"
        );

        printf(
            "1. Message from Supervisory controller "
            "(i.e. this node) to Elecvator Controller\n"
        );

        printf(
            "2. Message from Elevator controller "
            "to all other nodes\n"
        );

        printf(
            "3. Message from Car controller to "
            "supervisory controller (this node)\n"
        );

        printf(
            "4. Message from floor 1 controller to "
            "supervisory controller (this node)\n"
        );

        printf(
            "5. Message from floor 2 controller to "
            "supervisory controller (this node)\n"
        );

        printf(
            "6. Message from floor 3 controller to "
            "supervisory controller (this node)\n"
        );

        printf("\nYour choice: ");

        scanf(
            "%d",
            &IdChoice
        );

        // Convert a valid menu selection into its CAN ID.
        if (IdChoice >= 1 && IdChoice <= 6)
        {
            switch (IdChoice)
            {
                case 1:
                    IDvalue = ID_SC_TO_EC;
                    return IDvalue;

                case 2:
                    IDvalue = ID_EC_TO_ALL;
                    return IDvalue;

                case 3:
                    IDvalue = ID_CC_TO_SC;
                    return IDvalue;

                case 4:
                    IDvalue = ID_F1_TO_SC;
                    return IDvalue;

                case 5:
                    IDvalue = ID_F2_TO_SC;
                    return IDvalue;

                case 6:
                    IDvalue = ID_F3_TO_SC;
                    return IDvalue;
            }
        }
        else
        {
            printf(
                "\nPLEASE SELECT FROM CHOICES 1-6 ONLY!\n\n"
            );

            sleep(3);
        }
    }
}


/**
 * @brief Allows the user to select an elevator floor command.
 *
 * Displays a menu containing the three available elevator floors.
 * The user's selection is converted into the corresponding CAN
 * floor command.
 *
 * @return The CAN command associated with the selected floor.
 */
int chooseMsg()
{
    int messageChoice = 0;
    int messageValue = 0;

    while (1)
    {
        system("@cls||clear");

        printf("\nChoose Message\n");
        printf("1. Go to floor 1\n");
        printf("2. Go to floor 2\n");
        printf("3. Go to floor 3\n");

        printf("\nYour choice: ");

        scanf(
            "%d",
            &messageChoice
        );

        // Convert the selected floor into its CAN command.
        if (messageChoice >= 1 && messageChoice <= 3)
        {
            switch (messageChoice)
            {
                case 1:
                    messageValue = GO_TO_FLOOR1;
                    return messageValue;
                    break;

                case 2:
                    messageValue = GO_TO_FLOOR2;
                    return messageValue;
                    break;

                case 3:
                    messageValue = GO_TO_FLOOR3;
                    return messageValue;
                    break;
            }
        }
        else
        {
            printf(
                "PLEASE SELECT FROM CHOICES 1-3 ONLY!\n\n"
            );

            sleep(3);
        }
    }
}


/**
 * @brief Converts an elevator floor number into a CAN command.
 *
 * Converts Floors 1, 2, and 3 into the corresponding GO_TO_FLOOR
 * command used on the elevator CAN network.
 *
 * If an invalid floor number is supplied, the function defaults
 * to the Floor 1 command.
 *
 * @param floorVal Elevator floor number to convert.
 *
 * @return GO_TO_FLOOR1 for Floor 1.
 * @return GO_TO_FLOOR2 for Floor 2.
 * @return GO_TO_FLOOR3 for Floor 3.
 * @return GO_TO_FLOOR1 if floorVal is invalid.
 */
int HexFromFloor(int floorVal)
{
    switch (floorVal)
    {
        case 1:
            return GO_TO_FLOOR1;
            break;

        case 2:
            return GO_TO_FLOOR2;
            break;

        case 3:
            return GO_TO_FLOOR3;
            break;

        default:
            // Reset to Floor 1 when an invalid value is supplied.
            return GO_TO_FLOOR1;
    }
}


/**
 * @brief Converts a CAN floor command into an elevator floor number.
 *
 * Converts the GO_TO_FLOOR CAN command values back into their
 * corresponding elevator floor numbers.
 *
 * If an invalid CAN command is supplied, the function defaults
 * to Floor 1.
 *
 * @param Hex CAN floor command to convert.
 *
 * @return 1 if the command represents Floor 1.
 * @return 2 if the command represents Floor 2.
 * @return 3 if the command represents Floor 3.
 * @return 1 if the command is invalid.
 */
int FloorFromHex(int Hex)
{
    switch (Hex)
    {
        case GO_TO_FLOOR1:
            return 1;
            break;

        case GO_TO_FLOOR2:
            return 2;
            break;

        case GO_TO_FLOOR3:
            return 3;
            break;

        default:
            // Reset to Floor 1 when an invalid value is supplied.
            return 1;
    }
}