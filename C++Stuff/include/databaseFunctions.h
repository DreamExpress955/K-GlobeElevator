#ifndef DB_FUNCTIONS

#define DB_FUNCTIONS
#include <cstdint>
int db_getFloorNum();
void db_setFloorNum(int floorNum);
void db_logCANMessage(int nodeID, int messageID, int dataLength, uint8_t* data, const char* description);



#endif
