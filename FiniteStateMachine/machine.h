// Header file for Finite State Machine
#ifndef MACHINE_H
#define MACHINE_H

#include <iostream>

// Machine states for the elevator
enum MachineState {
    DoorsClosed, // 0
    DoorsOpened, // 1
    CarMoving,   // 2
    CarStopped,  // 3
};

// Menu Functions
void Menu();
void FloorReq();
void InsideReq();
void PrintState(MachineState state);


#endif // MACHINE_H
