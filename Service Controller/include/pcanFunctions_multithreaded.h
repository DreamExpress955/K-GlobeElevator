#ifndef PCAN_FUNCTIONS_MULTITHREADED_H
#define PCAN_FUNCTIONS_MULTITHREADED_H

#include <libpcan.h>

#define PCAN_RECEIVE_QUEUE_EMPTY 0x00020U
#define PCAN_NO_ERROR            0x00000U

#define ID_SC_TO_EC  0x100
#define ID_EC_TO_ALL 0x101
#define ID_CC_TO_SC  0x200
#define ID_F1_TO_SC  0x201
#define ID_F2_TO_SC  0x202
#define ID_F3_TO_SC  0x203

#define GO_TO_FLOOR1 0x05
#define GO_TO_FLOOR2 0x06
#define GO_TO_FLOOR3 0x07

int pcanTx(int id, int data);
void pcanRxWithDetailsMultithreaded();
void stopPcanRxThreads();
void stopPcanMultithreaded();

#endif
