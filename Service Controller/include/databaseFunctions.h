#ifndef DB_FUNCTIONS

#define DB_FUNCTIONS
int db_getFloorNum();
int db_setFloorNum(int floorNum);
void db_logCANMessage(int nodeID, int messageID, int dataLength, uint8_t* data, const char* description);
#endif
