#include "../include/pcanFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <stdlib.h>  
#include <errno.h>
#include <unistd.h> 
#include <signal.h>
#include <string.h>
#include <fcntl.h>    					// O_RDWR
#include <unistd.h>
#include <ctype.h>
#include <libpcan.h>   					// PCAN library
#include <queue>


// Globals
// ***********************************************************************************************************
HANDLE h;
HANDLE h2;
TPCANMsg Txmsg;
TPCANMsg Rxmsg;
DWORD status;
int elev = 0; //this is ths flag for the elevator controller, so that it only prints once
int elev2 = 0;
std::queue<TPCANMsg> canQueue;
// Code
// ***********************************************************************************************************

// Functions
// *****************************************************************
int pcanTx(int id, int data){
	h = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);		// Open PCAN channel

	// Initialize an opened CAN 2.0 channel with a 125kbps bitrate, accepting standard frames
	status = CAN_Init(h, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

	// Clear the channel - new - Must clear the channel before Tx/Rx
	status = CAN_Status(h);

	// Set up message
	Txmsg.ID = id; 	
	Txmsg.MSGTYPE = MSGTYPE_STANDARD; 
	Txmsg.LEN = 1; 
	Txmsg.DATA[0] = data; 

	sleep(1);  
	status = CAN_Write(h, &Txmsg);

	// Close CAN 2.0 channel and exit	
	CAN_Close(h);
	
	return (int)status;
}

int pcanRx(int num_msgs){
	int i = 0;

	// Open a CAN channel 
	h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

	// Initialize an opened CAN 2.0 channel with a 125kbps bitrate, accepting standard frames
	status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

	// Clear the channel - new - Must clear the channel before Tx/Rx
	status = CAN_Status(h2);

	// Clear screen to show received messages
	system("@cls||clear");

	// receive CAN message  - CODE adapted from PCAN BASIC C++ examples pcanread.cpp
	printf("\nReady to receive message(s) over CAN bus\n");
	
	// Read 'num' messages on the CAN bus
	while(i < num_msgs) {
		while((status = CAN_Read(h2, &Rxmsg)) == PCAN_RECEIVE_QUEUE_EMPTY){
			sleep(1);
		}
		if(status != PCAN_NO_ERROR) {						// If there is an error, display the code
			printf("Error 0x%x\n", (int)status);
			//break;
		}
										
		if(Rxmsg.ID != 0x01 && Rxmsg.LEN != 0x04) {		// Ignore status message on bus	
			printf("  - R ID:%4x LEN:%1x DATA:%02x \n",	// Display the CAN message
				(int)Rxmsg.ID, 
				(int)Rxmsg.LEN,
				(int)Rxmsg.DATA[0]);
		i++;
		}
	}
	

	// Close CAN 2.0 channel and exit	
	CAN_Close(h2);
	//printf("\nEnd Rx\n");
	return ((int)Rxmsg.DATA[0]);						// Return the last value received
}

TPCANMsg pcanRxWithDetails() {
	int i = 0;
	TPCANMsg msg;
	
	// Open a CAN channel 
	h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

	// Initialize an opened CAN 2.0 channel with a 125kbps bitrate, accepting standard frames
	status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

	// Clear the channel - new - Must clear the channel before Tx/Rx
	status = CAN_Status(h2);

	// Clear screen to show received messages
	//system("@cls||clear");

	// receive CAN message  - CODE adapted from PCAN BASIC C++ examples pcanread.cpp
	//printf("\nReady to receive message(s) over CAN bus\n");
	
	// Read 'num' messages on the CAN bus
	while(i < 1) {
		while((status = CAN_Read(h2, &Rxmsg)) == PCAN_RECEIVE_QUEUE_EMPTY){
			sleep(1);
		}
		if(status != PCAN_NO_ERROR) {						// If there is an error, display the code
			printf("Error 0x%x\n", (int)status);
			//break;
		}
										
		if(Rxmsg.ID != 0x01 && Rxmsg.LEN != 0x04) {		// Ignore status message on bus	
			canQueue.push(Rxmsg);						//add message to the queue
			msg = canQueue.front();

			switch (msg.ID) {
				case 0x0100:
					printf("Supervisoury Controller requested floor ");
					elev = 0;
					switch (msg.DATA[0]) {
						case 0x5:
							printf("1");
							break;
						case 0x6:
							printf("2");
							break;
						case 0x7:
							printf("3");
							break;
					}
					break;
				case 0x0101:
					if 	(elev == 0){
						printf("Elevator Controller announces the elevator is at floor ");
						elev = 1;
						elev2 =1;
						switch (msg.DATA[0]) {
							case 0x5:
								printf("1");
								break;
							case 0x6:
								printf("2");
								break;
							case 0x7:
								printf("3");
								break;
							}
							printf("  - R ID:%4x LEN:%1x DATA:%02x \n",	// Display the CAN message
					(int)msg.ID, 
					(int)msg.LEN,
					(int)msg.DATA[0]);
						}
					break;
				case 0x0200:
					printf("Car Controller requested floor ");
					elev = 0;
					switch (msg.DATA[0]) {
						case 0x5:
							printf("1");
							break;
						case 0x6:
							printf("2");
							break;
						case 0x7:
							printf("3");
							break;
					}
					break;
				case 0x0201:
					elev = 0;
					printf("Floor 1 Controller made a request");
					break;
				case 0x0202:
					elev = 0;
					printf("Floor 2 Controller made a request");
					break;
				case 0x0203:
					elev = 0;
					printf("Floor 3 Controller made a request");
					break;
			}
			if (elev == 0){
			printf("  - R ID:%4x LEN:%1x DATA:%02x \n",	// Display the CAN message
				(int)msg.ID, 
				(int)msg.LEN,
				(int)msg.DATA[0]);
			}

		canQueue.pop();					//remove the message from the queue	
		i++;
		}
	}
	

	// Close CAN 2.0 channel and exit	
	CAN_Close(h2);
	//printf("\nEnd Rx\n");
	return (msg);						// Return the last value received
}


