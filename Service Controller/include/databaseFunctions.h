#ifndef DB_FUNCTIONS
#include <cstdint>

#define DB_FUNCTIONS
int db_getFloorNum();
int db_setFloorNum(int floorNum);

void db_logCANMessage(int nodeID, int messageID, int dataLength, uint8_t* data, const char* description);

int db_getRequestedFloor();
int db_getRequestType();
int db_clearWebsiteRequest();

int db_getStopFlag();

#endif
